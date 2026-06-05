import React, { useEffect, useRef, useState } from 'react';
import tw from 'twin.macro';
import { animate, stagger } from 'animejs';
import { Actions, useStoreActions } from 'easy-peasy';
import { CheckCircleIcon, CodeIcon, ExclamationIcon } from '@heroicons/react/outline';
import FlashMessageRender from '@/components/FlashMessageRender';
import ServerContentBlock from '@/components/elements/ServerContentBlock';
import Spinner from '@/components/elements/Spinner';
import Input from '@/components/elements/Input';
import InputSpinner from '@/components/elements/InputSpinner';
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
    const size = large ? tw`h-10 w-10` : tw`h-7 w-7`;
    const imageSize = large ? tw`h-8 w-8` : tw`h-5 w-5`;

    if (software?.icon) {
        return (
            <span
                title={software.name}
                css={[tw`inline-flex flex-shrink-0 items-center justify-center overflow-hidden rounded`, size]}
                style={{ backgroundColor: software.color ?? undefined }}
            >
                <img src={software.icon} alt={software.name} css={[tw`object-contain`, imageSize]} />
            </span>
        );
    }

    return (
        <span
            title={software?.name ?? 'Server jar'}
            css={[tw`inline-flex flex-shrink-0 items-center justify-center rounded bg-neutral-300 font-bold text-neutral-900`, size]}
            style={{ backgroundColor: software?.color ?? undefined }}
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
    const [selectedEggId, setSelectedEggId] = useState<number>();
    const [version, setVersion] = useState('latest');
    const [resetConfirmed, setResetConfirmed] = useState(false);

    const load = () => {
        setLoading(true);
        setError(undefined);

        getServerVersions(uuid)
            .then((response) => {
                setData(response);
                setSelectedEggId(response.current.egg_id ?? response.software[0]?.id);
                setVersion(response.current.version || 'latest');
                setResetConfirmed(false);
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

        animate(shellRef.current, {
            opacity: [0, 1],
            translateY: [16, 0],
            duration: 420,
            ease: 'outCubic',
        });

        animate(shellRef.current.querySelectorAll('.software-tile'), {
            opacity: [0, 1],
            translateY: [14, 0],
            scale: [0.96, 1],
            delay: stagger(45),
            duration: 360,
            ease: 'outBack',
        });
    }, [data]);

    const selectSoftware = (software: ServerVersionSoftware) => {
        setSelectedEggId(software.id);

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
        if (!selectedEggId) return;

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
            });
    };

    if (loading && !data) {
        return <Spinner centered size={Spinner.Size.LARGE} />;
    }

    if (error && !data) {
        return <ServerError title={'Versions unavailable'} message={httpErrorToHuman(error)} onRetry={load} />;
    }

    const selectedSoftware = data?.software.find((software) => software.id === selectedEggId);
    const invalidVersion = !/^[A-Za-z0-9._+-]{1,32}$/.test(version.trim());

    return (
        <ServerContentBlock title={'Versions'} icon={CodeIcon} showFlashKey={FLASH_KEY}>
            <FlashMessageRender byKey={FLASH_KEY} css={tw`mb-4`} />
            <Dialog.Confirm
                open={confirmVisible}
                title={'Install selected version?'}
                confirm={'Install & Reinstall'}
                onClose={() => setConfirmVisible(false)}
                onConfirmed={install}
            >
                This will reinstall the server using {selectedSoftware?.name ?? 'the selected software'} {version.trim()}.
                Existing files can be replaced by the egg install script.
            </Dialog.Confirm>

            <div ref={shellRef} css={tw`mx-auto max-w-5xl opacity-0`}>
                <div css={tw`mb-6 flex flex-col gap-3 md:flex-row md:items-end md:justify-between`}>
                    <div>
                        <p css={tw`text-xs uppercase text-neutral-400`}>Minecraft software</p>
                        <h2 css={tw`mt-1 text-2xl font-semibold text-neutral-50`}>Version changer</h2>
                    </div>
                    <p css={tw`max-w-lg text-sm text-neutral-300`}>
                        Pick server software, choose a Minecraft version, then reinstall from MCJars metadata.
                    </p>
                </div>

                <div css={tw`grid gap-6 lg:grid-cols-5`}>
                    <div css={tw`lg:col-span-3`}>
                        <div css={tw`grid gap-3 sm:grid-cols-2`}>
                            {data?.software.map((software) => {
                                const selected = software.id === selectedEggId;

                                return (
                                    <button
                                        key={software.id}
                                        type={'button'}
                                        data-software-id={software.id}
                                        className={'software-tile'}
                                        css={[
                                            tw`min-h-[104px] rounded border border-neutral-700 bg-neutral-800 p-4 text-left transition-colors duration-150 hover:border-primary-400 hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-primary-400`,
                                            selected && tw`border-primary-400 bg-neutral-700`,
                                        ]}
                                        onClick={() => selectSoftware(software)}
                                    >
                                        <div css={tw`flex items-start justify-between gap-3`}>
                                            <div css={tw`flex items-center gap-3`}>
                                                <SoftwareIcon software={software} />
                                                <div>
                                                    <p css={tw`font-semibold text-neutral-50`}>{software.name}</p>
                                                    <p css={tw`text-xs uppercase text-neutral-400`}>{software.type}</p>
                                                </div>
                                            </div>
                                            {selected && <CheckCircleIcon css={tw`h-5 w-5 text-primary-300`} />}
                                        </div>
                                        <p css={tw`mt-3 line-clamp-2 text-xs text-neutral-300`}>{software.description}</p>
                                    </button>
                                );
                            })}
                        </div>
                    </div>

                    <div css={tw`lg:col-span-2`}>
                        <InputSpinner visible={loading || installing}>
                            <div css={tw`rounded border border-neutral-700 bg-neutral-800 p-5`}>
                                <div css={tw`flex items-center gap-3`}>
                                    <SoftwareIcon software={selectedSoftware} large />
                                    <div css={tw`min-w-0`}>
                                        <p css={tw`truncate font-semibold text-neutral-50`}>
                                            {selectedSoftware?.name ?? 'Select software'}
                                        </p>
                                        <p css={tw`text-xs text-neutral-400`}>
                                            Current: {data?.current.name ?? 'Unknown'} {data?.current.version ?? 'latest'}
                                        </p>
                                    </div>
                                </div>

                                <div css={tw`mt-5`}>
                                    <label css={tw`mb-2 block text-xs uppercase text-neutral-400`}>Minecraft version</label>
                                    <Input
                                        value={version}
                                        disabled={installing}
                                        hasError={invalidVersion}
                                        onChange={(event) => setVersion(event.currentTarget.value)}
                                        placeholder={'latest'}
                                    />
                                    <p css={tw`mt-2 text-xs text-neutral-400`}>Use latest or a version like 1.21.4.</p>
                                </div>

                                <div css={tw`mt-5 rounded border border-red-900 bg-red-900/20 p-4`}>
                                    <div css={tw`mb-2 flex items-center text-sm text-red-300`}>
                                        <ExclamationIcon css={tw`mr-2 h-5 w-5`} />
                                        <span>Reinstall required</span>
                                    </div>
                                    <label css={tw`flex cursor-pointer items-start gap-3 text-sm text-neutral-200`}>
                                        <Input
                                            type={'checkbox'}
                                            checked={resetConfirmed}
                                            disabled={installing}
                                            onChange={(event) => setResetConfirmed(event.currentTarget.checked)}
                                            css={tw`mt-0.5`}
                                        />
                                        <span>Reset the server, and delete all files (worlds, configs, plugins etc)</span>
                                    </label>
                                </div>

                                <div css={tw`mt-5 flex items-center justify-between gap-4`}>
                                    <p css={tw`text-xs text-neutral-400`}>Powered by MC Utils</p>
                                    <Button
                                        type={'button'}
                                        disabled={!selectedEggId || invalidVersion || !resetConfirmed || installing}
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
