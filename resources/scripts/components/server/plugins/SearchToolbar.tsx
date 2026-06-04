import React, { useState } from 'react';
import { Button } from '@/components/elements/button/index';
import Input from '@/components/elements/Input';
import { AdjustmentsIcon, PuzzleIcon, SearchIcon } from '@heroicons/react/outline';

interface Props {
    query: string;
    searching: boolean;
    onQueryChange: (value: string) => void;
    installedOpen: boolean;
    installedCount: number;
    filtersActiveCount: number;
    onSearch: () => void;
    onToggleInstalled: () => void;
    onOpenFilters: () => void;
}

export default ({
    query,
    searching,
    installedOpen,
    installedCount,
    filtersActiveCount,
    onQueryChange,
    onSearch,
    onToggleInstalled,
    onOpenFilters,
}: Props) => {
    const [focused, setFocused] = useState(false);
    const showIcon = !focused && query.length === 0;

    return (
        <div className={'bg-gray-700 rounded-box backdrop p-4'}>
            <div className={'flex flex-col md:flex-row gap-3'}>
                <div className={'relative flex-1'}>
                    {showIcon && <SearchIcon className={'absolute left-3 top-3 w-5 text-gray-400'} />}
                    <Input
                        value={query}
                        onFocus={() => setFocused(true)}
                        onBlur={() => setFocused(false)}
                        onChange={(e) => onQueryChange(e.currentTarget.value)}
                        onKeyDown={(e) => e.key === 'Enter' && onSearch()}
                        className={showIcon ? 'pl-10' : 'pl-3'}
                    />
                </div>
                <Button onClick={onSearch} disabled={searching}>
                    {searching ? 'Searching' : 'Search'}
                </Button>
                <Button.Text onClick={onOpenFilters}>
                    <AdjustmentsIcon className={'w-5 mr-2'} />
                    Filters
                    {filtersActiveCount > 0 && (
                        <span className={'ml-2 rounded-full bg-primary-500 px-2 py-0.5 text-xs text-white'}>
                            {filtersActiveCount}
                        </span>
                    )}
                </Button.Text>
                <Button.Text onClick={onToggleInstalled} className={installedOpen ? 'bg-gray-600' : undefined}>
                    <PuzzleIcon className={'w-5 mr-2'} />
                    Installed Plugins
                    {installedCount > 0 && (
                        <span className={'ml-2 rounded-full bg-gray-600 px-2 py-0.5 text-xs text-gray-200'}>
                            {installedCount}
                        </span>
                    )}
                </Button.Text>
            </div>
        </div>
    );
};
