import React, { useEffect, useMemo, useState } from 'react';
import tw from 'twin.macro';
import FlashMessageRender from '@/components/FlashMessageRender';
import ServerContentBlock from '@/components/elements/ServerContentBlock';
import Spinner from '@/components/elements/Spinner';
import Modal from '@/components/elements/Modal';
import { Dialog } from '@/components/elements/dialog';
import { ServerContext } from '@/state/server';
import useFlash from '@/plugins/useFlash';
import {
    deleteInstalledPlugin as deleteTrackedInstalledPlugin,
    getInstalledPlugins,
    InstalledPlugin,
    renameInstalledPlugin as renameTrackedInstalledPlugin,
    updateInstalledPlugin,
} from '@/api/server/plugins/installed';
import {
    getMarketplaceVersions,
    installMarketplacePlugin,
    MarketplacePlatform,
    MarketplacePlugin,
    MarketplaceVersion,
    searchMarketplacePlugins,
} from '@/api/server/plugins/marketplace';
import { PuzzleIcon } from '@heroicons/react/outline';
import { useTranslation } from 'react-i18next';
import { installedKey } from '@/components/server/plugins/utils';
import SearchToolbar from '@/components/server/plugins/SearchToolbar';
import InstalledPluginsPanel from '@/components/server/plugins/InstalledPluginsPanel';
import MarketplaceFilters from '@/components/server/plugins/MarketplaceFilters';
import MarketplacePluginCard from '@/components/server/plugins/MarketplacePluginCard';
import MarketplaceVersionModal from '@/components/server/plugins/MarketplaceVersionModal';
import PaginationControls from '@/components/server/plugins/PaginationControls';

const significantNameParts = (value: string): string[] =>
    value
        .toLowerCase()
        .replace(/\.jar$/, '')
        .replace(/[._-]+/g, ' ')
        .split(/\s+/)
        .map(installedKey)
        .filter((part) => part.length >= 4 && !['bukkit', 'paper', 'spigot', 'plugin', 'simple'].includes(part));

export default () => {
    const { t } = useTranslation('arix/server/plugins');
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { addFlash, clearFlashes, clearAndAddHttpError } = useFlash();
    const [installedPlugins, setInstalledPlugins] = useState<InstalledPlugin[]>([]);
    const [query, setQuery] = useState('');
    const [platform, setPlatform] = useState<MarketplacePlatform>('modrinth');
    const [version, setVersion] = useState('');
    const [loader, setLoader] = useState('paper');
    const [page, setPage] = useState(1);
    const [plugins, setPlugins] = useState<MarketplacePlugin[]>([]);
    const [loading, setLoading] = useState(true);
    const [searching, setSearching] = useState(false);
    const [selected, setSelected] = useState<MarketplacePlugin | null>(null);
    const [versions, setVersions] = useState<MarketplaceVersion[]>([]);
    const [versionsLoading, setVersionsLoading] = useState(false);
    const [installing, setInstalling] = useState<string | null>(null);
    const [fileAction, setFileAction] = useState<string | null>(null);
    const [pendingDelete, setPendingDelete] = useState<string | null>(null);
    const [showInstalledMenu, setShowInstalledMenu] = useState(false);
    const [showMobileFilters, setShowMobileFilters] = useState(false);

    const installedFiles = useMemo(() => installedPlugins.map((plugin) => plugin.filename), [installedPlugins]);
    const installedSet = useMemo(() => new Set(installedFiles.map(installedKey)), [installedFiles]);
    const platformUsesLoader = ['modrinth', 'hangar'].includes(platform);

    const refreshInstalled = () => {
        getInstalledPlugins(uuid)
            .then(setInstalledPlugins)
            .catch(() => setInstalledPlugins([]));
    };

    const performSearch = (searchPage = page, searchQuery = query) => {
        clearFlashes('plugins');

        setSearching(true);
        searchMarketplacePlugins(uuid, {
            platform,
            query: searchQuery,
            page: searchPage,
            version,
            loader: platformUsesLoader ? loader : '',
        })
            .then((response) => setPlugins(response.data))
            .catch((error) => clearAndAddHttpError({ key: 'plugins', error }))
            .then(() => setSearching(false));
    };

    useEffect(() => {
        clearFlashes('plugins');
        getInstalledPlugins(uuid)
            .then(setInstalledPlugins)
            .catch(() => setInstalledPlugins([]))
            .then(() => setLoading(false));
    }, []);

    useEffect(() => {
        if (!loading) performSearch();
    }, [loading, platform, page]);

    useEffect(() => {
        if (loading) return;

        const timeout = setTimeout(() => {
            if (page === 1) {
                performSearch(1);
            } else {
                setPage(1);
            }
        }, 500);

        return () => clearTimeout(timeout);
    }, [query]);

    const openVersions = (plugin: MarketplacePlugin) => {
        setSelected(plugin);
        setVersions([]);
        setVersionsLoading(true);
        clearFlashes('plugins');
        getMarketplaceVersions(
            uuid,
            plugin.platform,
            plugin.id,
            version,
            ['modrinth', 'hangar'].includes(plugin.platform) ? loader : ''
        )
            .then(setVersions)
            .catch((error) => clearAndAddHttpError({ key: 'plugins', error }))
            .then(() => setVersionsLoading(false));
    };

    const installLatest = (plugin: MarketplacePlugin) => {
        setInstalling(plugin.id);
        clearFlashes('plugins');
        installMarketplacePlugin(
            uuid,
            plugin.platform,
            plugin.id,
            null,
            version,
            ['modrinth', 'hangar'].includes(plugin.platform) ? loader : ''
        )
            .then(({ filename }) => {
                addFlash({
                    key: 'plugins',
                    type: 'success',
                    title: 'Install started',
                    message: `${filename} is being pulled into /plugins.`,
                });
                refreshInstalled();
            })
            .catch((error) => clearAndAddHttpError({ key: 'plugins', error }))
            .then(() => setInstalling(null));
    };

    const installVersion = (plugin: MarketplacePlugin, item: MarketplaceVersion) => {
        setInstalling(item.id);
        clearFlashes('plugins');
        installMarketplacePlugin(
            uuid,
            plugin.platform,
            plugin.id,
            item.id,
            version,
            ['modrinth', 'hangar'].includes(plugin.platform) ? loader : ''
        )
            .then(({ filename }) => {
                addFlash({
                    key: 'plugins',
                    type: 'success',
                    title: 'Install started',
                    message: `${filename} is being pulled into /plugins.`,
                });
                setSelected(null);
                refreshInstalled();
            })
            .catch((error) => clearAndAddHttpError({ key: 'plugins', error }))
            .then(() => setInstalling(null));
    };

    const marketplaceInstalled = (plugin: MarketplacePlugin): boolean => {
        if (
            installedPlugins.some(
                (item) =>
                    item.tracked &&
                    item.platform === plugin.platform &&
                    !!item.project &&
                    [plugin.id, plugin.slug].includes(item.project)
            )
        ) {
            return true;
        }

        const names = [plugin.slug, plugin.name].map(installedKey).filter(Boolean);
        const nameParts = Array.from(new Set([plugin.slug, plugin.name].flatMap(significantNameParts)));

        return Array.from(installedSet).some(
            (file) =>
                names.some((name) => file.includes(name) || name.includes(file)) ||
                (nameParts.length > 0 && nameParts.every((part) => file.includes(part)))
        );
    };

    const requestDeleteInstalledPlugin = (file: string) => {
        setPendingDelete(file);
    };

    const deleteInstalledPlugin = () => {
        if (!pendingDelete) return;

        const file = pendingDelete;

        setFileAction(file);
        setPendingDelete(null);
        clearFlashes('plugins');
        deleteTrackedInstalledPlugin(uuid, file)
            .then(() => {
                addFlash({ key: 'plugins', type: 'success', title: 'Plugin deleted', message: `${file} was deleted.` });
                refreshInstalled();
            })
            .catch((error) => clearAndAddHttpError({ key: 'plugins', error }))
            .then(() => setFileAction(null));
    };

    const renameInstalledPlugin = (from: string, to: string) => {
        setFileAction(from);
        clearFlashes('plugins');
        renameTrackedInstalledPlugin(uuid, from, to)
            .then(() => {
                addFlash({
                    key: 'plugins',
                    type: 'success',
                    title: 'Plugin renamed',
                    message: `${from} was renamed to ${to}.`,
                });
                refreshInstalled();
            })
            .catch((error) => clearAndAddHttpError({ key: 'plugins', error }))
            .then(() => setFileAction(null));
    };

    const updateInstalled = (file: string) => {
        setFileAction(file);
        clearFlashes('plugins');
        updateInstalledPlugin(uuid, file)
            .then(({ filename, updated }) => {
                addFlash({
                    key: 'plugins',
                    type: 'success',
                    title: updated ? 'Plugin update started' : 'Plugin already current',
                    message: updated
                        ? `${filename} is being pulled into /plugins.`
                        : `${file} is already on the latest tracked version.`,
                });
                refreshInstalled();
            })
            .catch((error) => clearAndAddHttpError({ key: 'plugins', error }))
            .then(() => setFileAction(null));
    };

    return (
        <ServerContentBlock title={t('plugins')} icon={PuzzleIcon}>
            <FlashMessageRender byKey={'plugins'} css={tw`mb-4`} />
            {loading ? (
                <Spinner size={'large'} centered />
            ) : (
                <div className={'grid grid-cols-1 gap-5'}>
                    <div className={'space-y-5 min-w-0'}>
                        <SearchToolbar
                            query={query}
                            searching={searching}
                            installedOpen={showInstalledMenu}
                            installedCount={installedPlugins.length}
                            filtersActiveCount={
                                [
                                    version,
                                    platform !== 'modrinth' ? platform : '',
                                    platformUsesLoader && loader !== 'paper' ? loader : '',
                                ].filter(Boolean).length
                            }
                            onQueryChange={setQuery}
                            onSearch={() => {
                                setPage(1);
                                performSearch(1);
                            }}
                            onToggleInstalled={() => setShowInstalledMenu(true)}
                            onOpenFilters={() => setShowMobileFilters(true)}
                        />

                        <div className={'grid grid-cols-1 lg:grid-cols-2 gap-4'}>
                            {searching ? (
                                <div className={'lg:col-span-2 py-12'}>
                                    <Spinner centered />
                                </div>
                            ) : (
                                plugins.map((plugin) => (
                                    <MarketplacePluginCard
                                        key={`${plugin.platform}:${plugin.id}`}
                                        plugin={plugin}
                                        installed={marketplaceInstalled(plugin) || plugin.installed}
                                        installing={installing === plugin.id}
                                        onInstallLatest={installLatest}
                                        onSelectVersion={openVersions}
                                    />
                                ))
                            )}
                            {!searching && plugins.length === 0 && (
                                <p className={'lg:col-span-2 text-center text-sm text-gray-300 py-12'}>
                                    No marketplace plugins found.
                                </p>
                            )}
                        </div>

                        <PaginationControls
                            page={page}
                            searching={searching}
                            hasNext={plugins.length >= 12}
                            onPrevious={() => setPage((value) => Math.max(1, value - 1))}
                            onNext={() => setPage((value) => value + 1)}
                        />
                    </div>

                    {showMobileFilters && (
                        <Modal visible appear onDismissed={() => setShowMobileFilters(false)} top>
                            <MarketplaceFilters
                                mode={'sheet'}
                                platform={platform}
                                version={version}
                                loader={loader}
                                onPlatformChange={(value) => {
                                    setPlatform(value);
                                    setPage(1);
                                }}
                                onVersionChange={setVersion}
                                onLoaderChange={setLoader}
                                onReset={() => {
                                    setVersion('');
                                    setLoader('paper');
                                    setPage(1);
                                }}
                            />
                        </Modal>
                    )}

                    {showInstalledMenu && (
                        <Modal visible appear onDismissed={() => setShowInstalledMenu(false)} top>
                            <InstalledPluginsPanel
                                plugins={installedPlugins}
                                busy={fileAction}
                                onDelete={requestDeleteInstalledPlugin}
                                onRename={renameInstalledPlugin}
                                onUpdate={updateInstalled}
                            />
                        </Modal>
                    )}

                    <Dialog.Confirm
                        open={!!pendingDelete}
                        onClose={() => setPendingDelete(null)}
                        title={'Delete plugin'}
                        confirm={'Delete'}
                        onConfirmed={deleteInstalledPlugin}
                    >
                        {pendingDelete ? `Delete ${pendingDelete} from /plugins? This cannot be undone.` : ''}
                    </Dialog.Confirm>

                    {selected && (
                        <MarketplaceVersionModal
                            plugin={selected}
                            versions={versions}
                            installed={installedSet}
                            loading={versionsLoading}
                            installing={!!installing}
                            onDismissed={() => setSelected(null)}
                            onInstall={(version) => installVersion(selected, version)}
                        />
                    )}
                </div>
            )}
        </ServerContentBlock>
    );
};
