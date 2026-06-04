import React, { useState } from 'react';
import { Button } from '@/components/elements/button/index';
import Input from '@/components/elements/Input';
import { PencilIcon, PuzzleIcon, RefreshIcon, TrashIcon } from '@heroicons/react/outline';
import { InstalledPlugin } from '@/api/server/plugins/installed';

interface Props {
    plugins: InstalledPlugin[];
    busy: string | null;
    onDelete: (file: string) => void;
    onRename: (from: string, to: string) => void;
    onUpdate: (file: string) => void;
}

export default ({ plugins, busy, onDelete, onRename, onUpdate }: Props) => {
    const [renaming, setRenaming] = useState<string | null>(null);
    const [name, setName] = useState('');

    const startRename = (file: string) => {
        setRenaming(file);
        setName(file);
    };

    const submitRename = (file: string) => {
        if (name.trim() !== '' && name !== file) {
            onRename(file, name.trim());
        }

        setRenaming(null);
    };

    return (
        <div className={'bg-gray-700 rounded-box backdrop p-5'}>
            <p className={'font-medium text-gray-100 mb-3'}>Installed Plugins</p>
            <div className={'grid gap-2'}>
                {plugins.length > 0 ? (
                    plugins.map((plugin) => {
                        const file = plugin.filename;

                        return (
                            <div key={file} className={'bg-gray-600 rounded-box px-4 py-3'}>
                                <div className={'flex flex-col lg:flex-row lg:items-center gap-3'}>
                                    <div className={'flex items-center gap-3 min-w-0 flex-1'}>
                                        <PuzzleIcon className={'w-5 text-gray-300 flex-shrink-0'} />
                                        {renaming === file ? (
                                            <Input
                                                value={name}
                                                onChange={(e) => setName(e.currentTarget.value)}
                                                onKeyDown={(e) => e.key === 'Enter' && submitRename(file)}
                                            />
                                        ) : (
                                            <div className={'min-w-0'}>
                                                <span className={'text-sm text-gray-100 truncate block'}>{file}</span>
                                                {plugin.tracked && (
                                                    <span className={'text-xs text-gray-400'}>
                                                        {plugin.platform} · {plugin.version}
                                                    </span>
                                                )}
                                            </div>
                                        )}
                                        {plugin.updateAvailable && (
                                            <span
                                                className={
                                                    'inline-flex h-4 w-4 flex-shrink-0 items-center justify-center rounded-full bg-yellow-500 bg-opacity-20 ring-1 ring-yellow-400 ring-opacity-60'
                                                }
                                                title={'Update available'}
                                                aria-label={'Update available'}
                                            >
                                                <span className={'h-2 w-2 rounded-full bg-yellow-300'} />
                                            </span>
                                        )}
                                    </div>
                                    <div className={'flex flex-wrap gap-2'}>
                                        {renaming === file ? (
                                            <>
                                                <Button.Text
                                                    disabled={busy !== null}
                                                    onClick={() => submitRename(file)}
                                                >
                                                    Save
                                                </Button.Text>
                                                <Button.Text disabled={busy !== null} onClick={() => setRenaming(null)}>
                                                    Cancel
                                                </Button.Text>
                                            </>
                                        ) : (
                                            <>
                                                <Button.Text
                                                    disabled={
                                                        busy !== null || !plugin.tracked || !plugin.updateAvailable
                                                    }
                                                    onClick={() => onUpdate(file)}
                                                >
                                                    <RefreshIcon className={'w-4 mr-1'} />
                                                    Update
                                                </Button.Text>
                                                <Button.Text disabled={busy !== null} onClick={() => startRename(file)}>
                                                    <PencilIcon className={'w-4 mr-1'} />
                                                    Rename
                                                </Button.Text>
                                                <Button.Danger disabled={busy !== null} onClick={() => onDelete(file)}>
                                                    <TrashIcon className={'w-4 mr-1'} />
                                                    Delete
                                                </Button.Danger>
                                            </>
                                        )}
                                    </div>
                                </div>
                            </div>
                        );
                    })
                ) : (
                    <p className={'text-sm text-gray-300'}>No plugins installed in /plugins.</p>
                )}
            </div>
        </div>
    );
};
