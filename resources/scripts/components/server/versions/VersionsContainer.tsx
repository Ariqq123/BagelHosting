import React, { useEffect, useRef, useState } from 'react';
import tw from 'twin.macro';
import { animate } from 'animejs/animation';
import type { JSAnimation } from 'animejs/animation';
import { stagger } from 'animejs/utils';
import { Actions, useStoreActions } from 'easy-peasy';
import { CheckCircleIcon, CodeIcon, ExclamationIcon } from '@heroicons/react/outline';
import FlashMessageRender from '@/components/FlashMessageRender';
import ServerContentBlock from '@/components/elements/ServerContentBlock';
import Spinner from '@/components/elements/Spinner';
import Input from '@/components/elements/Input';
import InputSpinner from '@/components/elements/InputSpinner';
import Select from '@/components/elements/Select';
import { Button } from '@/components/elements/button';
import { Dialog } from '@/components/elements/dialog';
import { ServerError } from '@/components/elements/ScreenBlock';
import { ApplicationStore } from '@/state';
import { ServerContext } from '@/state/server';
import { httpErrorToHuman } from '@/api/http';
import {
    getServerVersions,
    installServerVersion,
    ServerVersionSoftware,
    ServerVersionsResponse,
} from '@/api/server/versions';

const FLASH_KEY = 'server:versions';

const fallbackLabel = (software?: ServerVersionSoftware) => (software?.type || software?.name || 'J').charAt(0);

const SoftwareIcon = ({ software, large }: { software?: ServerVersionSoftware; large?: boolean }) => {
    const size = large ? tw`h-12 w-12` : tw`h-9 w-9`;
    const imageSize = large ? tw`h-10 w-10` : tw`h-7 w-7`;
    const shell = tw`inline-flex flex-shrink-0 items-center justify-center overflow-hidden rounded border border-primary-400 bg-primary-500/10 shadow-sm`;

    if (software?.icon) {
        return (
            <span
                title={software.name}
                css={[shell, size]}
            >
                <img src={software.icon} alt={software.name} css={[tw`object-contain`, imageSize]} />
            </span>
        );
    }

    return (
        <span
            title={software?.name ?? 'Server jar'}
            css={[shell, tw`font-bold text-neutral-100`, size]}
        >
            {fallbackLabel(software)}
        </span>
    );
};

const VersionsContainer = () => {
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const shellRef = useRef<HTMLDivElement>(null);
    const { addFlash, clearFlashes } = useStoreActions((actions: Actions<ApplicationStore>) => actions.flashes);
    const [data, setData] = useState<ServerVersionsResponse>();
    const [error, setError] = useState<Error>();
    const [loading, setLoading] = useState(true);
    const [installing, setInstalling] = useState(false);
    const [confirmVisible, setConfirmVisible] = useState(false);
    const [intentConfirmed, setIntentConfirmed] = useState(false);
    const [selectedEggId, setSelectedEggId] = useState<number>();
    const [version, setVersion] = useState('latest');
    const [showSnapshots, setShowSnapshots] = useState(false);

    const load = () => {
        setLoading(true);
        setError(undefined);

        getServerVersions(uuid)
            .then((response) => {
                setData(response);
                setSelectedEggId(response.current.egg_id ?? response.software[0]?.id);
                setVersion(response.current.version || 'latest');
            })
            .catch((error) => {
                console.error(error);
                setError(error);
            })
            .then(() => setLoading(false));
    };

    useEffect(() => {
        clearFlashes(FLASH_KEY);
        load();
    }, []);

    useEffect(() => {
        if (!data || !shellRef.current) return;

        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (prefersReducedMotion) {
            shellRef.current.style.opacity = '1';
            shellRef.current.style.transform = 'none';
            shellRef.current.querySelectorAll<HTMLElement>('.software-tile').forEach((tile) => {
                tile.style.opacity = '1';
                tile.style.transform = 'none';
            });

            return;
        }

        const animations: JSAnimation[] = [];

        animations.push(animate(shellRef.current, {
            opacity: [0, 1],
            translateY: [16, 0],
            duration: 420,
            ease: 'outCubic',
        }));

        animations.push(animate(shellRef.current.querySelectorAll('.software-tile'), {
            opacity: [0, 1],
            translateY: [14, 0],
            scale: [0.96, 1],
            delay: stagger(45),
            duration: 360,
            ease: 'outBack',
        }));

        return () => animations.forEach((animation) => animation.cancel());
    }, [data]);

    const selectSoftware = (software: ServerVersionSoftware) => {
        setSelectedEggId(software.id);
        setIntentConfirmed(false);
        setShowSnapshots(false);

        if (!shellRef.current) return;

        const tile = shellRef.current.querySelector(`[data-software-id="${software.id}"]`);
        if (tile) {
            animate(tile, {
                scale: [0.98, 1.03, 1],
                duration: 360,
                ease: 'outElastic(1, .7)',
            });
        }
    };

    const install = () => {
        if (!selectedEggId || !intentConfirmed) return;

        setInstalling(true);
        clearFlashes(FLASH_KEY);

        installServerVersion(uuid, selectedEggId, version.trim())
            .then(() => {
                addFlash({
                    key: FLASH_KEY,
                    type: 'success',
                    message: 'Version install started. The server is reinstalling with the selected software.',
                });
                load();
            })
            .catch((error) => {
                console.error(error);
                addFlash({ key: FLASH_KEY, type: 'error', message: httpErrorToHuman(error) });
            })
            .then(() => {
                setInstalling(false);
                setConfirmVisible(false);
                setIntentConfirmed(false);
            });
    };

    const closeConfirm = () => {
        if (installing) return;

        setConfirmVisible(false);
        setIntentConfirmed(false);
    };

    if (loading && !data) {
        return <Spinner centered size={Spinner.Size.LARGE} />;
    }

    if (error && !data) {
        return <ServerError title={'Versions unavailable'} message={httpErrorToHuman(error)} onRetry={load} />;
    }

    const selectedSoftware = data?.software.find((software) => software.id === selectedEggId);
    const versionOptions = selectedSoftware?.versions ?? [];
    const visibleVersionOptions = versionOptions.filter((item) => showSnapshots || item.type !== 'SNAPSHOT');
    const selectedVersionOption = versionOptions.find((item) => item.id === version);
    const usesCustomVersion = version !== 'latest' && !visibleVersionOptions.some((item) => item.id === version);
    const invalidVersion = !/^[A-Za-z0-9._+-]{1,32}$/.test(version.trim());

    const toggleSnapshots = (enabled: boolean) => {
        setShowSnapshots(enabled);

        if (!enabled && selectedVersionOption?.type === 'SNAPSHOT') {
            setVersion('latest');
        }
    };

    return (
        <ServerContentBlock title={'Versions'} icon={CodeIcon} showFlashKey={FLASH_KEY}>
            <FlashMessageRender byKey={FLASH_KEY} css={tw`mb-4`} />
            <Dialog
                open={confirmVisible}
                title={'Confirm version install'}
                onClose={closeConfirm}
            >
                <div css={tw`space-y-4 text-sm text-neutral-200`}>
                    <p>
                        Install {selectedSoftware?.name ?? 'the selected software'} {version.trim()} and reinstall this server.
                    </p>
                    <label css={tw`flex cursor-pointer items-start gap-3 rounded border border-neutral-700 bg-neutral-800 p-3`}>
                        <Input
                            type={'checkbox'}
                            checked={intentConfirmed}
                            disabled={installing}
                            onChange={(event) => setIntentConfirmed(event.currentTarget.checked)}
                            css={tw`mt-0.5`}
                        />
                        <span>I understand the installer may replace files managed by the selected egg.</span>
                    </label>
                </div>
                <Dialog.Footer>
                    <Button.Text type={'button'} disabled={installing} onClick={closeConfirm}>Cancel</Button.Text>
                    <Button.Danger type={'button'} disabled={!intentConfirmed || installing} onClick={install}>
                        Install & Reinstall
                    </Button.Danger>
                </Dialog.Footer>
            </Dialog>

            <div ref={shellRef} css={tw`mx-auto max-w-5xl`}>
                <div css={tw`mb-4 flex flex-col gap-2 md:mb-6 md:flex-row md:items-end md:justify-between md:gap-3`}>
                    <div>
                        <p css={tw`font-header text-xs font-semibold uppercase tracking-wide text-neutral-400`}>Minecraft software</p>
                        <h2 css={tw`font-header mt-1 text-2xl font-bold text-neutral-50 md:text-3xl`}>Version changer</h2>
                    </div>
                    <p css={tw`max-w-lg text-xs text-neutral-300 md:text-sm`}>
                        Select software and Minecraft version. MCJars handles the download.
                    </p>
                </div>

                <div css={tw`grid gap-4 lg:grid-cols-5 lg:gap-6`}>
                    <div css={tw`lg:col-span-3`}>
                        <div css={tw`grid gap-2 sm:grid-cols-2 md:gap-3`}>
                            {data?.software.map((software) => {
                                const selected = software.id === selectedEggId;

                                return (
                                    <button
                                        key={software.id}
                                        type={'button'}
                                        data-software-id={software.id}
                                        aria-pressed={selected}
                                        className={'software-tile'}
                                        css={[
                                            tw`rounded border border-neutral-700 bg-neutral-800 p-3 text-left transition-colors duration-150 hover:border-primary-400 hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-primary-400 md:min-h-[112px] md:p-4`,
                                            selected && tw`border-primary-400 bg-neutral-700`,
                                        ]}
                                        onClick={() => selectSoftware(software)}
                                    >
                                        <div css={tw`flex items-center justify-between gap-3 md:items-start`}>
                                            <div css={tw`flex items-center gap-3`}>
                                                <SoftwareIcon software={software} />
                                                <div>
                                                    <p css={tw`font-header text-lg font-bold leading-tight text-neutral-50`}>{software.name}</p>
                                                    <p css={tw`mt-0.5 font-header text-xs font-semibold uppercase tracking-wide text-neutral-400`}>{software.type}</p>
                                                </div>
                                            </div>
                                            {selected && <CheckCircleIcon css={tw`h-5 w-5 text-primary-300`} />}
                                        </div>
                                        <p css={tw`mt-3 hidden line-clamp-2 text-xs text-neutral-300 md:block`}>{software.description}</p>
                                    </button>
                                );
                            })}
                        </div>
                    </div>

                    <div css={tw`lg:col-span-2`}>
                        <InputSpinner visible={loading || installing}>
                            <div css={tw`rounded border border-neutral-700 bg-neutral-800 p-4 md:p-5`}>
                                <div css={tw`flex items-center gap-3`}>
                                    <SoftwareIcon software={selectedSoftware} large />
                                    <div css={tw`min-w-0`}>
                                        <p css={tw`font-header truncate text-lg font-bold text-neutral-50`}>
                                            {selectedSoftware?.name ?? 'Select software'}
                                        </p>
                                        <p css={tw`text-xs text-neutral-400`}>
                                            Current: {data?.current.name ?? 'Unknown'} {data?.current.version ?? 'latest'}
                                        </p>
                                    </div>
                                </div>

                                <div css={tw`mt-4 md:mt-5`}>
                                    <label css={tw`mb-2 block text-xs uppercase text-neutral-400`}>Minecraft version</label>
                                    <Select
                                        value={usesCustomVersion ? 'custom' : version}
                                        disabled={installing || versionOptions.length === 0}
                                        onChange={(event) => setVersion(event.currentTarget.value === 'custom' ? '' : event.currentTarget.value)}
                                    >
                                        <option value={'latest'}>Latest</option>
                                        {visibleVersionOptions.map((item) => (
                                            <option key={item.id} value={item.id}>{item.id}{item.type === 'SNAPSHOT' ? ' snapshot' : ''}</option>
                                        ))}
                                        <option value={'custom'}>Custom version</option>
                                    </Select>
                                    <label css={tw`mt-3 flex cursor-pointer items-center gap-2 text-xs text-neutral-300`}>
                                        <Input
                                            type={'checkbox'}
                                            checked={showSnapshots}
                                            disabled={installing || versionOptions.length === 0}
                                            onChange={(event) => toggleSnapshots(event.currentTarget.checked)}
                                        />
                                        <span>Show snapshots</span>
                                    </label>
                                    {(usesCustomVersion || versionOptions.length === 0 || version === '') && (
                                        <Input
                                            css={tw`mt-2`}
                                            value={version}
                                            disabled={installing}
                                            hasError={invalidVersion}
                                            onChange={(event) => setVersion(event.currentTarget.value)}
                                            placeholder={'1.21.4'}
                                        />
                                    )}
                                    <p css={tw`mt-2 text-xs text-neutral-400`}>Choose Latest or a listed Minecraft version.</p>
                                </div>

                                <div css={tw`mt-4 rounded border border-red-900 bg-red-900/20 p-3 md:mt-5 md:p-4`}>
                                    <div css={tw`mb-2 flex items-center text-sm text-red-300`}>
                                        <ExclamationIcon css={tw`mr-2 h-5 w-5`} />
                                        <span>Reinstall required</span>
                                    </div>
                                    <p css={tw`text-sm text-neutral-200`}>
                                        The panel reinstalls the server with the selected egg. Create a backup first if you need one.
                                    </p>
                                </div>

                                <div css={tw`mt-4 flex items-center justify-between gap-4 md:mt-5`}>
                                    <a
                                        href={'https://mcjars.app'}
                                        target={'_blank'}
                                        rel={'noreferrer'}
                                        css={tw`inline-flex items-center gap-1.5 text-xs text-neutral-400 transition-colors hover:text-neutral-200`}
                                    >
                                        <img src={'https://mcjars.app/favicon.ico'} alt={''} css={tw`h-4 w-4 rounded-sm`} />
                                        <span>mcjars.app</span>
                                    </a>
                                    <Button
                                        type={'button'}
                                        disabled={!selectedEggId || invalidVersion || installing}
                                        onClick={() => setConfirmVisible(true)}
                                    >
                                        Install
                                    </Button>
                                </div>
                            </div>
                        </InputSpinner>
                    </div>
                </div>
            </div>
        </ServerContentBlock>
    );
};

export default VersionsContainer;
