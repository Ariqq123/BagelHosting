import React from 'react';
import { ServerPlugin } from '@/api/server/plugins/getServerPlugins';
import { Button } from '@/components/elements/button/index';
import { CloudDownloadIcon } from '@heroicons/react/outline';
import { installedKey } from '@/components/server/plugins/utils';

interface Props {
    plugins: ServerPlugin[];
    installed: Set<string>;
    downloading: number | null;
    labels: {
        noPlugins: string;
        downloading: string;
        download: string;
    };
    onDownload: (plugin: ServerPlugin) => void;
}

export default ({ plugins, installed, downloading, labels, onDownload }: Props) => (
    <div className={'bg-gray-700 rounded-box backdrop p-5'}>
        <p className={'font-medium text-gray-100 mb-3'}>Panel Plugin Catalog</p>
        <div className={'grid gap-3'}>
            {plugins.length > 0 ? (
                plugins.map((plugin) => (
                    <div
                        key={plugin.id}
                        className={
                            'flex flex-col md:flex-row md:items-center justify-between gap-3 bg-gray-600 rounded-box p-4'
                        }
                    >
                        <div className={'min-w-0'}>
                            <p className={'font-medium text-gray-100 truncate'}>{plugin.name}</p>
                            <p className={'text-sm text-gray-300'}>{plugin.description}</p>
                            <p className={'text-xs text-gray-400 mt-1'}>{plugin.filename}</p>
                        </div>
                        {installed.has(installedKey(plugin.filename)) ? (
                            <span className={'text-sm text-green-300'}>Plugin Installed</span>
                        ) : (
                            <Button disabled={downloading !== null} onClick={() => onDownload(plugin)}>
                                <CloudDownloadIcon className={'w-5 mr-2'} />
                                {downloading === plugin.id ? labels.downloading : labels.download}
                            </Button>
                        )}
                    </div>
                ))
            ) : (
                <p className={'text-sm text-gray-300'}>{labels.noPlugins}</p>
            )}
        </div>
    </div>
);
