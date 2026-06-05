import React, { useEffect, useState } from 'react';
import tw from 'twin.macro';
import { Actions, useStoreActions } from 'easy-peasy';
import { CodeIcon, ExclamationIcon } from '@heroicons/react/outline';
import FlashMessageRender from '@/components/FlashMessageRender';
import ServerContentBlock from '@/components/elements/ServerContentBlock';
import TitledGreyBox from '@/components/elements/TitledGreyBox';
import Spinner from '@/components/elements/Spinner';
import Input from '@/components/elements/Input';
import Select from '@/components/elements/Select';
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

const iconForSoftware = (software?: ServerVersionSoftware) => {
    const key = software?.name.toLowerCase() ?? '';

    if (key.includes('paper')) return { label: 'P', name: 'Paper', css: tw`bg-green-500 text-green-900` };
    if (key.includes('purpur')) return { label: 'P', name: 'Purpur', css: tw`bg-purple-500 text-purple-900` };
    if (key.includes('vanilla')) return { label: 'V', name: 'Vanilla', css: tw`bg-yellow-400 text-yellow-900` };
    if (key.includes('fabric')) return { label: 'F', name: 'Fabric', css: tw`bg-yellow-500 text-yellow-900` };
    if (key.includes('forge')) return { label: 'F', name: 'Forge', css: tw`bg-red-400 text-red-900` };
    if (key.includes('velocity')) return { label: 'V', name: 'Velocity', css: tw`bg-cyan-400 text-cyan-900` };

    return { label: 'J', name: 'Server jar', css: tw`bg-neutral-300 text-neutral-900` };
};

const SoftwareIcon = ({ software }: { software?: ServerVersionSoftware }) => {
    const icon = iconForSoftware(software);

    return (
        <span
            title={icon.name}
            css={[tw`inline-flex h-6 w-6 flex-shrink-0 items-center justify-center rounded text-xs font-bold`, icon.css]}
        >
            {icon.label}
        </span>
    );
};

const VersionsContainer = () => {
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
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
            <div css={tw`mx-auto w-full max-w-xl`}>
                <TitledGreyBox title={'Version changer'} css={tw`relative`}>
                    <InputSpinner visible={loading || installing}>
                        <p css={tw`mb-5 text-sm text-neutral-200`}>
                            Easily switch your server to a different Minecraft version with just one click.
                        </p>
                        <div css={tw`space-y-3`}>
                            <div css={tw`relative`}>
                                <span css={tw`pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2`}>
                                    <SoftwareIcon software={selectedSoftware} />
                                </span>
                                <Select
                                    css={tw`pl-12`}
                                    value={selectedEggId ?? ''}
                                    disabled={!data?.software.length || installing}
                                    onChange={(event) => setSelectedEggId(Number(event.currentTarget.value))}
                                >
                                    {data?.software.map((software) => (
                                        <option key={software.id} value={software.id}>
                                            {iconForSoftware(software).label} - {software.name}
                                        </option>
                                    ))}
                                </Select>
                            </div>
                            <div>
                                <Input
                                    value={version}
                                    disabled={installing}
                                    hasError={invalidVersion}
                                    onChange={(event) => setVersion(event.currentTarget.value)}
                                    placeholder={'Select a version'}
                                />
                            </div>
                            <div css={tw`rounded border border-neutral-600 bg-neutral-700/40 p-4`}>
                                <div css={tw`mb-2 flex items-center text-sm text-red-300`}>
                                    <ExclamationIcon css={tw`mr-2 h-5 w-5`} />
                                    <span>Danger zone</span>
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
                        </div>
                        <div css={tw`mt-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between`}>
                            <p css={tw`text-sm text-neutral-200`}>
                                Powered by <strong css={tw`font-semibold text-neutral-100`}>MC Utils</strong>
                            </p>
                            <Button
                                type={'button'}
                                disabled={!selectedEggId || invalidVersion || !resetConfirmed || installing}
                                onClick={() => setConfirmVisible(true)}
                            >
                                Install
                            </Button>
                        </div>
                    </InputSpinner>
                </TitledGreyBox>
            </div>
        </ServerContentBlock>
    );
};

export default VersionsContainer;
