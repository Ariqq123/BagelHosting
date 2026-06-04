import React, { useEffect, useMemo, useState } from 'react';
import tw from 'twin.macro';
import FlashMessageRender from '@/components/FlashMessageRender';
import ServerContentBlock from '@/components/elements/ServerContentBlock';
import Spinner from '@/components/elements/Spinner';
import { Button } from '@/components/elements/button/index';
import Input from '@/components/elements/Input';
import Select from '@/components/elements/Select';
import Modal from '@/components/elements/Modal';
import { ServerContext } from '@/state/server';
import useFlash from '@/plugins/useFlash';
import { httpErrorToHuman } from '@/api/http';
import getServerPlugins, { ServerPlugin } from '@/api/server/plugins/getServerPlugins';
import downloadServerPlugin from '@/api/server/plugins/downloadServerPlugin';
import loadDirectory from '@/api/server/files/loadDirectory';
import {
    getMarketplaceVersions,
    installMarketplacePlugin,
    MarketplacePlatform,
    MarketplacePlugin,
    MarketplaceVersion,
    searchMarketplacePlugins,
} from '@/api/server/plugins/marketplace';
import {
    CloudDownloadIcon,
    ExternalLinkIcon,
    PuzzleIcon,
    SearchIcon,
    StarIcon,
    SwitchHorizontalIcon,
} from '@heroicons/react/outline';
import { useTranslation } from 'react-i18next';

const formatNumber = (value: number): string => new Intl.NumberFormat().format(value);
const formatDate = (value: string | null): string => (value ? new Date(value).toLocaleDateString() : 'Unknown');
const installedKey = (value: string): string =>
    value
        .toLowerCase()
        .replace(/\.jar$/, '')
        .replace(/[^a-z0-9]+/g, '');

export default () => {
    const { t } = useTranslation('arix/server/plugins');
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { addFlash, clearFlashes, clearAndAddHttpError } = useFlash();
    const [curated, setCurated] = useState<ServerPlugin[]>([]);
    const [installedFiles, setInstalledFiles] = useState<string[]>([]);
    const [query, setQuery] = useState('');
    const [platform, setPlatform] = useState<MarketplacePlatform>('modrinth');
    const [version, setVersion] = useState('');
    const [loader, setLoader] = useState('paper');
    const [page, setPage] = useState(1);
    const [plugins, setPlugins] = useState<MarketplacePlugin[]>([]);
    const [loading, setLoading] = useState(true);
    const [searching, setSearching] = useState(false);
    const [downloading, setDownloading] = useState<number | null>(null);
    const [selected, setSelected] = useState<MarketplacePlugin | null>(null);
    const [versions, setVersions] = useState<MarketplaceVersion[]>([]);
    const [versionsLoading, setVersionsLoading] = useState(false);
    const [installing, setInstalling] = useState<string | null>(null);
    const [showCurated, setShowCurated] = useState(false);

    const installedSet = useMemo(() => new Set(installedFiles.map(installedKey)), [installedFiles]);

    const refreshInstalled = () => {
        loadDirectory(uuid, '/plugins')
            .then((files) => setInstalledFiles(files.filter((file) => file.isFile).map((file) => file.name)))
            .catch(() => setInstalledFiles([]));
    };

    const performSearch = () => {
        clearFlashes('plugins');
        setSearching(true);
        searchMarketplacePlugins(uuid, {
            platform,
            query,
            page,
            version,
            loader: platform === 'modrinth' ? loader : '',
        })
            .then((response) => setPlugins(response.data))
            .catch((error) => clearAndAddHttpError({ key: 'plugins', error }))
            .then(() => setSearching(false));
    };

    useEffect(() => {
        clearFlashes('plugins');
        Promise.all([getServerPlugins(uuid), loadDirectory(uuid, '/plugins').catch(() => [])])
            .then(([plugins, files]) => {
                setCurated(plugins);
                setInstalledFiles(files.filter((file) => file.isFile).map((file) => file.name));
            })
            .catch((error) => {
                console.error(error);
                addFlash({ key: 'plugins', type: 'error', title: 'Error', message: httpErrorToHuman(error) });
            })
            .then(() => setLoading(false));
    }, []);

    useEffect(() => {
        if (!loading) performSearch();
    }, [platform, page]);

    const openVersions = (plugin: MarketplacePlugin) => {
        setSelected(plugin);
        setVersions([]);
        setVersionsLoading(true);
        clearFlashes('plugins');
        getMarketplaceVersions(uuid, plugin.platform, plugin.id, version, plugin.platform === 'modrinth' ? loader : '')
            .then(setVersions)
            .catch((error) => clearAndAddHttpError({ key: 'plugins', error }))
            .then(() => setVersionsLoading(false));
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
            plugin.platform === 'modrinth' ? loader : ''
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

    const onDownloadCurated = (plugin: ServerPlugin) => {
        clearFlashes('plugins');
        setDownloading(plugin.id);
        downloadServerPlugin(uuid, plugin.id)
            .then(() => {
                addFlash({
                    key: 'plugins',
                    type: 'success',
                    title: t('download-started'),
                    message: t('download-started-message', { filename: plugin.filename }),
                });
                refreshInstalled();
            })
            .catch((error) => clearAndAddHttpError({ key: 'plugins', error }))
            .then(() => setDownloading(null));
    };

    const marketplaceInstalled = (plugin: MarketplacePlugin): boolean => {
        const names = [plugin.slug, plugin.name].map(installedKey).filter(Boolean);
        return names.some((name) =>
            Array.from(installedSet).some((file) => file.includes(name) || name.includes(file))
        );
    };

    return (
        <ServerContentBlock title={t('plugins')} icon={PuzzleIcon}>
            <FlashMessageRender byKey={'plugins'} css={tw`mb-4`} />
            {loading ? (
                <Spinner size={'large'} centered />
            ) : (
                <div className={'grid grid-cols-1 xl:grid-cols-[1fr_18rem] gap-5'}>
                    <div className={'space-y-5 min-w-0'}>
                        <div className={'bg-gray-700 rounded-box backdrop p-4'}>
                            <div className={'flex flex-col md:flex-row gap-3'}>
                                <div className={'relative flex-1'}>
                                    <SearchIcon className={'absolute left-3 top-3 w-5 text-gray-400'} />
                                    <Input
                                        value={query}
                                        onChange={(e) => setQuery(e.currentTarget.value)}
                                        onKeyDown={(e) => e.key === 'Enter' && performSearch()}
                                        css={tw`pl-10`}
                                    />
                                </div>
                                <Button
                                    onClick={() => {
                                        setPage(1);
                                        performSearch();
                                    }}
                                    disabled={searching}
                                >
                                    {searching ? 'Searching' : 'Search'}
                                </Button>
                                <Button.Text onClick={() => setShowCurated((value) => !value)}>
                                    <PuzzleIcon className={'w-5 mr-2'} />
                                    Installed Plugins
                                </Button.Text>
                            </div>
                        </div>

                        {showCurated && (
                            <div className={'bg-gray-700 rounded-box backdrop p-5'}>
                                <p className={'font-medium text-gray-100 mb-3'}>Curated Plugins</p>
                                <div className={'grid gap-3'}>
                                    {curated.length > 0 ? (
                                        curated.map((plugin) => (
                                            <div
                                                key={plugin.id}
                                                className={
                                                    'flex flex-col md:flex-row md:items-center justify-between gap-3 bg-gray-600 rounded-box p-4'
                                                }
                                            >
                                                <div className={'min-w-0'}>
                                                    <p className={'font-medium text-gray-100 truncate'}>
                                                        {plugin.name}
                                                    </p>
                                                    <p className={'text-sm text-gray-300'}>{plugin.description}</p>
                                                    <p className={'text-xs text-gray-400 mt-1'}>{plugin.filename}</p>
                                                </div>
                                                {installedSet.has(installedKey(plugin.filename)) ? (
                                                    <span className={'text-sm text-green-300'}>Plugin Installed</span>
                                                ) : (
                                                    <Button
                                                        disabled={downloading !== null}
                                                        onClick={() => onDownloadCurated(plugin)}
                                                    >
                                                        <CloudDownloadIcon className={'w-5 mr-2'} />
                                                        {downloading === plugin.id ? t('downloading') : t('download')}
                                                    </Button>
                                                )}
                                            </div>
                                        ))
                                    ) : (
                                        <p className={'text-sm text-gray-300'}>{t('no-plugins')}</p>
                                    )}
                                </div>
                            </div>
                        )}

                        <div className={'grid grid-cols-1 lg:grid-cols-2 gap-4'}>
                            {searching ? (
                                <div className={'lg:col-span-2 py-12'}>
                                    <Spinner centered />
                                </div>
                            ) : (
                                plugins.map((plugin) => {
                                    const isInstalled = marketplaceInstalled(plugin) || plugin.installed;
                                    return (
                                        <div
                                            key={`${plugin.platform}:${plugin.id}`}
                                            className={
                                                'bg-gray-700 rounded-box backdrop p-5 flex flex-col gap-4 min-w-0'
                                            }
                                        >
                                            <div className={'flex items-start gap-4 min-w-0'}>
                                                {plugin.iconUrl ? (
                                                    <img
                                                        src={plugin.iconUrl}
                                                        alt=''
                                                        className={
                                                            'w-12 h-12 rounded object-cover bg-gray-600 flex-shrink-0'
                                                        }
                                                    />
                                                ) : (
                                                    <div
                                                        className={
                                                            'w-12 h-12 rounded bg-gray-600 flex items-center justify-center flex-shrink-0'
                                                        }
                                                    >
                                                        <PuzzleIcon className={'w-7 text-gray-300'} />
                                                    </div>
                                                )}
                                                <div className={'min-w-0 flex-1'}>
                                                    <div className={'flex items-center gap-2 min-w-0'}>
                                                        <p className={'font-medium text-gray-100 truncate'}>
                                                            {plugin.name}
                                                        </p>
                                                        <a
                                                            href={plugin.url}
                                                            target={'_blank'}
                                                            rel={'noreferrer'}
                                                            className={
                                                                'text-gray-300 hover:text-gray-100 flex-shrink-0'
                                                            }
                                                        >
                                                            <ExternalLinkIcon className={'w-4'} />
                                                        </a>
                                                    </div>
                                                    <p className={'text-xs uppercase text-gray-400'}>
                                                        {plugin.platform} {plugin.author && `by ${plugin.author}`}
                                                    </p>
                                                </div>
                                            </div>
                                            <p className={'text-sm text-gray-300 line-clamp-3 min-h-[3.75rem]'}>
                                                {plugin.description || 'No description provided.'}
                                            </p>
                                            <div className={'grid grid-cols-3 gap-2 text-xs text-gray-300'}>
                                                <span>{formatDate(plugin.updatedAt)}</span>
                                                <span>{formatNumber(plugin.downloads)} downloads</span>
                                                <span className={'flex items-center justify-end'}>
                                                    <StarIcon className={'w-4 mr-1'} />
                                                    {plugin.stars}
                                                </span>
                                            </div>
                                            {isInstalled ? (
                                                <div
                                                    className={
                                                        'text-sm text-green-300 bg-green-900 bg-opacity-30 rounded-component px-3 py-2 text-center'
                                                    }
                                                >
                                                    Plugin Installed
                                                </div>
                                            ) : (
                                                <Button onClick={() => openVersions(plugin)}>
                                                    <SwitchHorizontalIcon className={'w-5 mr-2'} />
                                                    Select Version
                                                </Button>
                                            )}
                                        </div>
                                    );
                                })
                            )}
                            {!searching && plugins.length === 0 && (
                                <p className={'lg:col-span-2 text-center text-sm text-gray-300 py-12'}>
                                    No marketplace plugins found.
                                </p>
                            )}
                        </div>

                        <div className={'flex justify-between'}>
                            <Button.Text
                                disabled={page <= 1 || searching}
                                onClick={() => setPage((value) => Math.max(1, value - 1))}
                            >
                                Previous
                            </Button.Text>
                            <span className={'text-sm text-gray-300 py-2'}>Page {page}</span>
                            <Button.Text
                                disabled={searching || plugins.length < 12}
                                onClick={() => setPage((value) => value + 1)}
                            >
                                Next
                            </Button.Text>
                        </div>
                    </div>

                    <div className={'bg-gray-700 rounded-box backdrop p-5 xl:sticky xl:top-4 h-max'}>
                        <p className={'font-medium text-gray-100 mb-4'}>Filters</p>
                        <div className={'space-y-4'}>
                            <div className={'grid grid-cols-2 gap-2'}>
                                {(['modrinth', 'spiget'] as MarketplacePlatform[]).map((item) => (
                                    <label
                                        key={item}
                                        className={
                                            'flex items-center gap-2 text-sm text-gray-200 bg-gray-600 rounded-component px-3 py-2'
                                        }
                                    >
                                        <Input
                                            type={'radio'}
                                            checked={platform === item}
                                            onChange={() => {
                                                setPlatform(item);
                                                setPage(1);
                                            }}
                                        />
                                        {item === 'spiget' ? 'Spigot' : 'Modrinth'}
                                    </label>
                                ))}
                            </div>
                            <div>
                                <p className={'text-xs uppercase text-gray-400 mb-2'}>Minecraft Version</p>
                                <Input
                                    value={version}
                                    placeholder={'1.21.4'}
                                    onChange={(e) => setVersion(e.currentTarget.value)}
                                />
                            </div>
                            <div>
                                <p className={'text-xs uppercase text-gray-400 mb-2'}>Loader</p>
                                <Select
                                    value={loader}
                                    disabled={platform !== 'modrinth'}
                                    onChange={(e) => setLoader(e.currentTarget.value)}
                                >
                                    <option value={'paper'}>Paper</option>
                                    <option value={'purpur'}>Purpur</option>
                                    <option value={'spigot'}>Spigot</option>
                                    <option value={'bukkit'}>Bukkit</option>
                                </Select>
                            </div>
                            <Button.Text
                                css={tw`w-full`}
                                onClick={() => {
                                    setVersion('');
                                    setLoader('paper');
                                    setPage(1);
                                }}
                            >
                                Reset Filters
                            </Button.Text>
                        </div>
                    </div>

                    {selected && (
                        <Modal visible appear onDismissed={() => setSelected(null)} showSpinnerOverlay={!!installing}>
                            <div className={'space-y-4'}>
                                <div>
                                    <p className={'text-lg font-medium text-gray-100'}>{selected.name}</p>
                                    <p className={'text-sm text-gray-300'}>
                                        Select a compatible version to install into /plugins.
                                    </p>
                                </div>
                                {versionsLoading ? (
                                    <Spinner centered />
                                ) : versions.length > 0 ? (
                                    <div className={'grid gap-2 max-h-[28rem] overflow-y-auto'}>
                                        {versions.map((item) => {
                                            const exists = installedSet.has(installedKey(item.filename));
                                            return (
                                                <div
                                                    key={item.id}
                                                    className={
                                                        'flex flex-col md:flex-row md:items-center justify-between gap-3 bg-gray-700 rounded-box p-3'
                                                    }
                                                >
                                                    <div className={'min-w-0'}>
                                                        <p className={'text-sm font-medium text-gray-100 truncate'}>
                                                            {item.name || item.versionNumber}
                                                        </p>
                                                        <p className={'text-xs text-gray-400 truncate'}>
                                                            {item.filename} · {formatDate(item.createdAt)}
                                                        </p>
                                                    </div>
                                                    {exists ? (
                                                        <span className={'text-sm text-green-300'}>
                                                            Plugin Installed
                                                        </span>
                                                    ) : (
                                                        <Button
                                                            disabled={!!installing}
                                                            onClick={() => installVersion(selected, item)}
                                                        >
                                                            {versions.length === 1 ? 'Install' : 'Install Version'}
                                                        </Button>
                                                    )}
                                                </div>
                                            );
                                        })}
                                    </div>
                                ) : (
                                    <p className={'text-sm text-gray-300'}>No compatible versions found.</p>
                                )}
                            </div>
                        </Modal>
                    )}
                </div>
            )}
        </ServerContentBlock>
    );
};
