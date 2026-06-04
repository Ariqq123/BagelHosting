import React from 'react';
import { MarketplacePlugin } from '@/api/server/plugins/marketplace';
import { Button } from '@/components/elements/button/index';
import {
    CalendarIcon,
    DownloadIcon,
    ExternalLinkIcon,
    PuzzleIcon,
    StarIcon,
    SwitchHorizontalIcon,
} from '@heroicons/react/outline';
import { formatNumber, formatRelativeDate } from '@/components/server/plugins/utils';

interface Props {
    plugin: MarketplacePlugin;
    installed: boolean;
    installing: boolean;
    onInstallLatest: (plugin: MarketplacePlugin) => void;
    onSelectVersion: (plugin: MarketplacePlugin) => void;
}

export default ({ plugin, installed, installing, onInstallLatest, onSelectVersion }: Props) => (
    <div className={'bg-gray-700 rounded-box backdrop p-5 flex flex-col min-w-0 min-h-[15rem]'}>
        <div className={'flex items-start gap-4 min-w-0'}>
            {plugin.iconUrl ? (
                <img
                    src={plugin.iconUrl}
                    alt=''
                    className={'w-16 h-16 rounded object-cover bg-gray-600 flex-shrink-0'}
                />
            ) : (
                <div className={'w-16 h-16 rounded bg-gray-600 flex items-center justify-center flex-shrink-0'}>
                    <PuzzleIcon className={'w-9 text-gray-300'} />
                </div>
            )}
            <div className={'min-w-0 flex-1 pt-1'}>
                <div className={'flex items-center gap-2 min-w-0'}>
                    <p className={'text-base md:text-lg font-medium text-gray-100 truncate'}>{plugin.name}</p>
                    <a
                        href={plugin.url}
                        target={'_blank'}
                        rel={'noreferrer'}
                        className={'text-gray-300 hover:text-gray-100 flex-shrink-0'}
                    >
                        <ExternalLinkIcon className={'w-4'} />
                    </a>
                </div>
                {plugin.author && (
                    <p className={'text-sm text-gray-400 truncate'}>
                        By <span className={'underline'}>{plugin.author}</span>
                    </p>
                )}
            </div>
        </div>

        <p className={'text-sm md:text-base text-gray-300 line-clamp-2 min-h-[2.75rem] mt-4'}>
            {plugin.description || 'No description provided.'}
        </p>

        <div className={'flex items-center text-sm text-gray-300 mt-3 min-w-0'}>
            <CalendarIcon className={'w-5 mr-1 flex-shrink-0'} />
            <span className={'truncate'}>Last updated {formatRelativeDate(plugin.updatedAt)}</span>
        </div>

        <div className={'mt-auto pt-5 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3'}>
            <div className={'flex items-center gap-4 text-sm md:text-base text-gray-300'}>
                <span className={'flex items-center'}>
                    <DownloadIcon className={'w-5 mr-1'} />
                    {formatNumber(plugin.downloads)}
                </span>
                <span className={'flex items-center'}>
                    <StarIcon className={'w-5 mr-1'} />
                    {formatNumber(plugin.stars)}
                </span>
            </div>

            {installed ? (
                <div
                    className={
                        'text-sm text-green-300 bg-green-900 bg-opacity-30 rounded-component px-4 py-2 text-center sm:min-w-[10rem]'
                    }
                >
                    Installed
                </div>
            ) : (
                <div className={'grid grid-cols-2 gap-2 sm:min-w-[14rem]'}>
                    <Button disabled={installing} onClick={() => onInstallLatest(plugin)}>
                        Install
                    </Button>
                    <Button.Text disabled={installing} onClick={() => onSelectVersion(plugin)}>
                        <SwitchHorizontalIcon className={'w-4 mr-1'} />
                        Versions
                    </Button.Text>
                </div>
            )}
        </div>
    </div>
);
