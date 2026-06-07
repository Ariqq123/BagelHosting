import React from 'react';
import Modal from '@/components/elements/Modal';
import Spinner from '@/components/elements/Spinner';
import { Button } from '@/components/elements/button/index';
import { MarketplacePlugin, MarketplaceVersion } from '@/api/server/plugins/marketplace';
import { formatDate, installedKey } from '@/components/server/plugins/utils';
import { useTranslation } from 'react-i18next';

interface Props {
    plugin: MarketplacePlugin;
    versions: MarketplaceVersion[];
    installed: Set<string>;
    loading: boolean;
    installing: boolean;
    onDismissed: () => void;
    onInstall: (version: MarketplaceVersion) => void;
}

export default function MarketplaceVersionModal({ plugin, versions, installed, loading, installing, onDismissed, onInstall }: Props) {
    const { t } = useTranslation('arix/server/plugins');

    return (
    <Modal visible appear onDismissed={onDismissed} showSpinnerOverlay={installing}>
        <div className={'space-y-4'}>
            <div>
                <p className={'text-lg font-medium text-gray-100'}>{plugin.name}</p>
                <p className={'text-sm text-gray-300'}>Select a compatible version to install into /plugins.</p>
            </div>
            {loading ? (
                <Spinner centered />
            ) : versions.length > 0 ? (
                <div className={'grid gap-2 max-h-[28rem] overflow-y-auto'}>
                    {versions.map((item) => {
                        const exists = installed.has(installedKey(item.filename));

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
                                    <span className={'text-sm text-green-300'}>{t('plugin-installed')}</span>
                                ) : (
                                    <Button disabled={installing} onClick={() => onInstall(item)}>
                                        {versions.length === 1 ? t('install.install-plugin') : t('install.select-version')}
                                    </Button>
                                )}
                            </div>
                        );
                    })}
                </div>
            ) : (
                <p className={'text-sm text-gray-300'}>{t('no-compatible-versions')}</p>
            )}
        </div>
    </Modal>
  );
}
