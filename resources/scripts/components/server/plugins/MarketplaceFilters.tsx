import React, { useState } from 'react';
import { Button } from '@/components/elements/button/index';
import Input from '@/components/elements/Input';
import { MarketplacePlatform } from '@/api/server/plugins/marketplace';
import { ChevronDownIcon, ChevronRightIcon } from '@heroicons/react/outline';
import { useTranslation } from 'react-i18next';

const MINECRAFT_VERSION_GROUPS = [
    { label: '26.1', versions: ['26.1.2', '26.1.1', '26.1'] },
    {
        label: '1.21',
        versions: [
            '1.21.11',
            '1.21.10',
            '1.21.9',
            '1.21.8',
            '1.21.7',
            '1.21.6',
            '1.21.5',
            '1.21.4',
            '1.21.3',
            '1.21.2',
            '1.21.1',
            '1.21',
        ],
    },
    { label: '1.20', versions: ['1.20.6', '1.20.5', '1.20.4', '1.20.3', '1.20.2', '1.20.1', '1.20'] },
    { label: '1.19', versions: ['1.19.4', '1.19.3', '1.19.2', '1.19.1', '1.19'] },
    { label: '1.18', versions: ['1.18.2', '1.18.1', '1.18'] },
    { label: '1.17', versions: ['1.17.1', '1.17'] },
    { label: '1.16', versions: ['1.16.5', '1.16.4', '1.16.3', '1.16.2', '1.16.1', '1.16'] },
    { label: '1.15', versions: ['1.15.2', '1.15.1', '1.15'] },
    { label: '1.14', versions: ['1.14.4', '1.14.3', '1.14.2', '1.14.1', '1.14'] },
    { label: '1.13', versions: ['1.13.2', '1.13.1', '1.13'] },
    { label: '1.12', versions: ['1.12.2', '1.12.1', '1.12'] },
    { label: '1.11', versions: ['1.11.2', '1.11.1', '1.11'] },
    { label: '1.10', versions: ['1.10.2', '1.10'] },
    { label: '1.9', versions: ['1.9.4', '1.9.2', '1.9'] },
    { label: '1.8', versions: ['1.8.9', '1.8.8'] },
];

const LOADERS = [
    'bukkit',
    'bungeecord',
    'folia',
    'paper',
    'purpur',
    'spigot',
    'sponge',
    'velocity',
    'waterfall',
    'fabric',
];

const PLATFORMS: MarketplacePlatform[] = ['modrinth', 'spiget', 'hangar', 'curseforge'];

const platformLabel = (platform: MarketplacePlatform): string =>
    ({ modrinth: 'Modrinth', spiget: 'Spigot', hangar: 'Hangar', curseforge: 'CurseForge' }[platform]);

interface Props {
    platform: MarketplacePlatform;
    version: string;
    loader: string;
    mode?: 'sidebar' | 'sheet';
    onPlatformChange: (platform: MarketplacePlatform) => void;
    onVersionChange: (version: string) => void;
    onLoaderChange: (loader: string) => void;
    onReset: () => void;
}

interface SectionProps {
    title: string;
    summary?: string;
    children: React.ReactNode;
}

const FilterSection = ({ title, summary, children }: SectionProps) => {
    const [open, setOpen] = useState(true);
    const Icon = open ? ChevronDownIcon : ChevronRightIcon;

    return (
        <div className={'bg-gray-600 rounded-component'}>
            <button
                type={'button'}
                className={'w-full flex items-center justify-between gap-3 px-3 py-2 text-left'}
                onClick={() => setOpen((value) => !value)}
            >
                <span className={'min-w-0'}>
                    <span className={'block text-xs uppercase text-gray-300'}>{title}</span>
                    {summary && <span className={'block text-xs text-gray-400 truncate'}>{summary}</span>}
                </span>
                <Icon className={'w-4 text-gray-300 flex-shrink-0'} />
            </button>
            {open && <div className={'px-3 pb-3'}>{children}</div>}
        </div>
    );
};

export default function MarketplaceFilters({
    platform,
    version,
    loader,
    mode = 'sidebar',
    onPlatformChange,
    onVersionChange,
    onLoaderChange,
    onReset,
}: Props) {
    const { t } = useTranslation('arix/server/plugins');
    const [mobileOpen, setMobileOpen] = useState(false);
    const sheet = mode === 'sheet';
    const MobileIcon = mobileOpen ? ChevronDownIcon : ChevronRightIcon;
    const [openVersion, setOpenVersion] = useState<string | null>(
        version ? version.split('.').slice(0, 2).join('.') : null
    );

    return (
        <div className={'bg-gray-700 rounded-box backdrop p-4 xl:p-5 xl:sticky xl:top-4 h-max'}>
            {sheet ? (
                <div className={'mb-4'}>
                    <p className={'font-medium text-gray-100'}>{t('reset-filters')}</p>
                    <p className={'text-xs text-gray-400'}>
                        {[platformLabel(platform), version || 'All versions'].join(' · ')}
                    </p>
                </div>
            ) : (
                <>
                    <button
                        type={'button'}
                        className={'xl:hidden w-full flex items-center justify-between gap-3 text-left'}
                        onClick={() => setMobileOpen((value) => !value)}
                    >
                        <span>
                            <span className={'block font-medium text-gray-100'}>{t('reset-filters')}</span>
                            <span className={'block text-xs text-gray-400'}>
                                {[platformLabel(platform), version || 'All versions'].join(' · ')}
                            </span>
                        </span>
                        <MobileIcon className={'w-5 text-gray-300 flex-shrink-0'} />
                    </button>
                    <p className={'hidden xl:block font-medium text-gray-100 mb-4'}>{t('reset-filters')}</p>
                </>
            )}
            <div className={sheet || mobileOpen ? 'space-y-4 mt-4 xl:mt-0' : 'hidden xl:block xl:space-y-4'}>
                <div className={'grid grid-cols-2 gap-2'}>
                    {PLATFORMS.map((item) => (
                        <label
                            key={item}
                            className={
                                'flex items-center gap-2 text-sm text-gray-200 bg-gray-600 rounded-component px-3 py-2'
                            }
                        >
                            <Input type={'radio'} checked={platform === item} onChange={() => onPlatformChange(item)} />
                            {platformLabel(item)}
                        </label>
                    ))}
                </div>

                <div>
                    <p className={'text-xs uppercase text-gray-400 mb-2'}>{t('version')}</p>
                    <div className={'space-y-2 max-h-72 overflow-y-auto pr-1'}>
                        <button
                            type={'button'}
                            className={
                                version === ''
                                    ? 'w-full text-left text-xs bg-primary-500 text-white rounded-component px-3 py-2'
                                    : 'w-full text-left text-xs bg-gray-600 text-gray-200 rounded-component px-3 py-2'
                            }
                            onClick={() => onVersionChange('')}
                        >
                            All versions
                        </button>
                        {MINECRAFT_VERSION_GROUPS.map((group) => {
                            const open = openVersion === group.label;
                            const active = version === group.label || group.versions.includes(version);

                            return (
                                <div key={group.label} className={'bg-gray-600 rounded-component overflow-hidden'}>
                                    <button
                                        type={'button'}
                                        className={
                                            active
                                                ? 'w-full flex items-center justify-between text-left text-xs bg-primary-500 text-white px-3 py-2'
                                                : 'w-full flex items-center justify-between text-left text-xs text-gray-200 px-3 py-2'
                                        }
                                        onClick={() => setOpenVersion(open ? null : group.label)}
                                    >
                                        <span>{group.label}</span>
                                        <span>{open ? '-' : '+'}</span>
                                    </button>
                                    {open && (
                                        <div className={'grid grid-cols-2 gap-2 p-2'}>
                                            {group.versions.map((item) => (
                                                <button
                                                    key={item}
                                                    type={'button'}
                                                    className={
                                                        version === item
                                                            ? 'text-xs bg-primary-500 text-white rounded-component px-2 py-1'
                                                            : 'text-xs bg-gray-700 text-gray-200 rounded-component px-2 py-1'
                                                    }
                                                    onClick={() => onVersionChange(item)}
                                                >
                                                    {item}
                                                </button>
                                            ))}
                                        </div>
                                    )}
                                </div>
                            );
                        })}
                    </div>
                </div>

                <FilterSection
                    title={t('loader')}
                    summary={['modrinth', 'hangar'].includes(platform) ? loader : 'Modrinth/Hangar only'}
                >
                    <div className={'grid grid-cols-2 gap-2'}>
                        {LOADERS.map((item) => (
                            <label
                                key={item}
                                className={
                                    'flex items-center gap-2 text-xs text-gray-200 bg-gray-700 rounded-component px-2 py-2 capitalize'
                                }
                            >
                                <Input
                                    type={'radio'}
                                    checked={loader === item}
                                    disabled={!['modrinth', 'hangar'].includes(platform)}
                                    onChange={() => onLoaderChange(item)}
                                />
                                {item}
                            </label>
                        ))}
                    </div>
                </FilterSection>

                <Button.Text className={'w-full'} onClick={onReset}>
                    Reset Filters
                </Button.Text>
            </div>
        </div>
    );
}
