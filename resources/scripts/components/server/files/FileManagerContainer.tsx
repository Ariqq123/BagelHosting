import React, { useEffect, useState } from 'react';
import { httpErrorToHuman } from '@/api/http';
import { CSSTransition } from 'react-transition-group';
import Spinner from '@/components/elements/Spinner';
import FileObjectRow from '@/components/server/files/FileObjectRow';
import FileManagerBreadcrumbs from '@/components/server/files/FileManagerBreadcrumbs';
import { FileObject } from '@/api/server/files/loadDirectory';
import NewDirectoryButton from '@/components/server/files/NewDirectoryButton';
import { NavLink, useLocation } from 'react-router-dom';
import Can from '@/components/elements/Can';
import { ServerError } from '@/components/elements/ScreenBlock';
import tw from 'twin.macro';
import { Button } from '@/components/elements/button/index';
import { ServerContext } from '@/state/server';
import useFileManagerSwr from '@/plugins/useFileManagerSwr';
import FileManagerStatus from '@/components/server/files/FileManagerStatus';
import MassActionsBar from '@/components/server/files/MassActionsBar';
import UploadButton from '@/components/server/files/UploadButton';
import ServerContentBlock from '@/components/elements/ServerContentBlock';
import { useStoreActions } from '@/state/hooks';
import ErrorBoundary from '@/components/elements/ErrorBoundary';
import { FileActionCheckbox } from '@/components/server/files/SelectFileCheckbox';
import Tooltip from '@/components/elements/tooltip/Tooltip';
import { LuFilePlus } from "react-icons/lu";
import { FolderOpenIcon, ArrowNarrowDownIcon, SearchIcon } from '@heroicons/react/outline';
import { Formik } from 'formik';
import Field from '@/components/elements/Field';
import { hashToPath } from '@/helpers';
import style from './style.module.css';
import { useTranslation } from 'react-i18next';

interface SortButtonProps {
    label: string;
    filterType: string;
    onClick: () => void;
  }
  
const SortButton: React.FC<SortButtonProps> = ({ label, filterType, onClick }) => (
    <button onClick={onClick} className={`flex items-center gap-x-1 text-sm text-gray-300 ${filterType === label ? '!text-gray-200' : filterType === `${label}-reversed` ? '!text-gray-200' : ''}`}>
        {label}
        <div className={`${filterType === label ? '' : filterType === `${label}-reversed` ? 'rotate-180' : 'opacity-50'}`}>
            <ArrowNarrowDownIcon className={'w-3'} />
        </div>
    </button>
);

export default () => {
    const { t } = useTranslation('arix/server/files');
    const [filterType, setFilterType] = useState('name');
    const id = ServerContext.useStoreState((state) => state.server.data!.id);
    const { hash } = useLocation();
    const { data: files, error, mutate } = useFileManagerSwr();
    const directory = ServerContext.useStoreState((state) => state.files.directory);
    const clearFlashes = useStoreActions((actions) => actions.flashes.clearFlashes);
    const setDirectory = ServerContext.useStoreActions((actions) => actions.files.setDirectory);

    const [searchTerm, setSearchTerm] = useState<string>('');
    const [filteredFiles, setFilteredFiles] = useState<FileObject[]>([]);

    const setSelectedFiles = ServerContext.useStoreActions((actions) => actions.files.setSelectedFiles);
    const selectedFilesLength = ServerContext.useStoreState((state) => state.files.selectedFiles.length);
    const sortFiles = (files: FileObject[], filterType: string): FileObject[] => {
        const commonSort = (filesToSort: FileObject[]) =>
            filesToSort.sort((a, b) => (a.isFile === b.isFile ? 0 : a.isFile ? 1 : -1));
    
        let sortedFiles: FileObject[];
    
        switch (filterType) {
            case 'name':
            case 'name-reversed':
                sortedFiles = files.sort((a, b) => a.name.localeCompare(b.name));
                break;
            case 'size':
            case 'size-reversed':
                sortedFiles = files.sort((a, b) => a.size - b.size);
                break;
            case 'date':
            case 'date-reversed':
                sortedFiles = files.sort((a, b) => new Date(b.modifiedAt).getTime() - new Date(a.modifiedAt).getTime());
                break;
            default:
                sortedFiles = files.sort((a, b) => a.name.localeCompare(b.name));
                break;
        }
    
        if (filterType.endsWith('-reversed')) {
            sortedFiles.reverse();
        }
    
        return commonSort(sortedFiles).filter((file, index) => index === 0 || file.name !== sortedFiles[index - 1].name);
    };
    
    useEffect(() => {
        clearFlashes('files');
        setSelectedFiles([]);
        setDirectory(hashToPath(hash));
    }, [hash]);

    useEffect(() => {
        mutate();
    }, [directory]);

    useEffect(() => {
        // Filter files based on the search term
        const filtered = files?.filter(
            (file) =>
                file.name.toLowerCase().includes(searchTerm.toLowerCase())
        );
    
        setFilteredFiles(filtered || []);
    }, [files, searchTerm]);

    const onSelectAllClick = (e: React.ChangeEvent<HTMLInputElement>) => {
        setSelectedFiles(e.currentTarget.checked ? files?.map((file) => file.name) || [] : []);
    };

    if (error) {
        return <ServerError message={httpErrorToHuman(error)} onRetry={() => mutate()} />;
    }

    function filterFiles( value: string ){
        if(value == filterType){
            setFilterType(value + '-reversed');
        } else {
            setFilterType(value);
        }
    }

    return (
        <ServerContentBlock title={t('file-manager')} showFlashKey={'files'} icon={FolderOpenIcon}>
            <div className={'bg-gray-700 backdrop rounded-box py-5 px-2 mb-4'}>
                <ErrorBoundary>
                    <div className={'flex flex-wrap-reverse gap-4 justify-between'}>
                        <FileManagerBreadcrumbs
                            renderLeft={
                                <FileActionCheckbox
                                    type={'checkbox'}
                                    css={tw`mx-4`}
                                    checked={selectedFilesLength === (files?.length === 0 ? -1 : files?.length)}
                                    onChange={onSelectAllClick}
                                />
                            }
                        />
                        <div className={style.manager_actions}>
                            <div className={'md:w-auto'}>
                                <Formik initialValues={{}} onSubmit={() => {}}>
                                    <Field
                                        type="text"
                                        icon={SearchIcon}
                                        placeholder={t('search')}
                                        name="Search files"
                                        value={searchTerm}
                                        className={'md:max-w-[250px] w-full'}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                    />
                                </Formik>
                            </div>
                            <div className={'h-full w-1 bg-gray-600 lg:block hidden'} />
                            <FileManagerStatus />
                            <MassActionsBar />
                            <Can action={'file.create'}>
                                <div className={'h-full w-1 bg-gray-600 lg:block hidden'} />
                                <NewDirectoryButton />
                                <UploadButton />
                                <Tooltip content={`${t('new-file')}`}>
                                    <NavLink to={`/server/${id}/files/new${window.location.hash}`}>
                                        <Button className={'h-full'}>
                                            <LuFilePlus />
                                        </Button>
                                    </NavLink>
                                </Tooltip>
                            </Can>
                        </div>
                    </div>
                </ErrorBoundary>
            </div>
            <div className={'bg-gray-700 backdrop rounded-box py-5 px-2'}>
                {!files ? (
                    <Spinner size={'large'} centered />
                ) : (
                    <>
                        <div className={'hidden sm:flex px-4 pb-2'}>
                            <div className={'ml-20 flex-1'}>
                                <SortButton label={t('name')} filterType={filterType} onClick={() => filterFiles('name')} />
                            </div>
                            <div className={'w-1/6 mr-4 justify-end flex'}>
                                <SortButton label={t('size')} filterType={filterType} onClick={() => filterFiles('size')} />
                            </div>
                            <div className={'w-1/5 mr-16 justify-end flex'}>
                                <SortButton label={t('date')} filterType={filterType} onClick={() => filterFiles('date')} />
                            </div>
                        </div>

                        {!files.length ? (
                            <p css={tw`text-sm text-neutral-300 text-center`}>{t('is-empty')}</p>
                        ) : (
                            <CSSTransition classNames={'fade'} timeout={150} appear in>
                                <div>
                                    {files.length > 250 && (
                                        <div css={tw`rounded bg-yellow-400 mb-px p-3`}>
                                            <p css={tw`text-yellow-900 text-sm text-center`}>
                                                {t('is-limited')}
                                            </p>
                                        </div>
                                    )}
                                    {sortFiles(filteredFiles.slice(0, 250), filterType).map(file => (
                                        <FileObjectRow key={file.key} file={file} />
                                    ))}
                                </div>
                            </CSSTransition>
                        )}
                    </>
                )}
            </div>
        </ServerContentBlock>
    );
};
