import React, { useEffect, useState } from 'react';
import tw from 'twin.macro';
import { Actions, useStoreActions } from 'easy-peasy';
import { CodeIcon } from '@heroicons/react/outline';
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
    ServerVersionsResponse,
} from '@/api/server/versions';

const FLASH_KEY = 'server:versions';

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
            <TitledGreyBox title={'Minecraft Version Installer'}>
                <InputSpinner visible={loading || installing}>
                    <div css={tw`grid gap-6 md:grid-cols-2`}>
                        <div>
                            <label css={tw`block text-xs uppercase text-neutral-300 mb-2`}>Software</label>
                            <Select
                                value={selectedEggId ?? ''}
                                disabled={!data?.software.length || installing}
                                onChange={(event) => setSelectedEggId(Number(event.currentTarget.value))}
                            >
                                {data?.software.map((software) => (
                                    <option key={software.id} value={software.id}>
                                        {software.name}
                                    </option>
                                ))}
                            </Select>
                            {selectedSoftware?.description && (
                                <p css={tw`mt-2 text-xs text-neutral-300`}>{selectedSoftware.description}</p>
                            )}
                        </div>
                        <div>
                            <label css={tw`block text-xs uppercase text-neutral-300 mb-2`}>Version</label>
                            <Input
                                value={version}
                                disabled={installing}
                                hasError={invalidVersion}
                                onChange={(event) => setVersion(event.currentTarget.value)}
                                placeholder={'latest'}
                            />
                            <p css={tw`mt-2 text-xs text-neutral-300`}>Use latest or a version like 1.21.4.</p>
                        </div>
                    </div>
                    <div css={tw`mt-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between`}>
                        <p css={tw`text-sm text-neutral-300`}>
                            Current: {data?.current.name ?? 'Unknown'} {data?.current.version ?? 'latest'}
                        </p>
                        <Button.Danger
                            type={'button'}
                            disabled={!selectedEggId || invalidVersion || installing}
                            onClick={() => setConfirmVisible(true)}
                        >
                            Install Version
                        </Button.Danger>
                    </div>
                </InputSpinner>
            </TitledGreyBox>
        </ServerContentBlock>
    );
};

export default VersionsContainer;
