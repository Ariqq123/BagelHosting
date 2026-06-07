import React, { useEffect, useState } from 'react';
import { useHistory } from 'react-router-dom';
import http from '@/api/http';
import PageContentBlock from '@/components/elements/PageContentBlock';
import Spinner from '@/components/elements/Spinner';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { 
    faServer, 
    faMicrochip, 
    faHdd, 
    faMemory, 
    faExclamationCircle,
    faCheckCircle,
    faChevronRight,
    faLayerGroup
} from '@fortawesome/free-solid-svg-icons';
import { useLanguage } from './i18n/LanguageContext';

interface Egg {
    id: number;
    egg_id: number;
    name: string;
    description: string;
    nest_name: string;
    memory: number;
    disk: number;
    cpu: number;
}

interface Node {
    id: number;
    name: string;
    location: string;
}

interface FreeServersData {
    enabled: boolean;
    max_servers: number;
    current_servers: number;
    can_create: boolean;
    remaining: number;
    eggs: Egg[];
    nodes: Node[];
    display_unit: string;
    language: string;
    message?: string;
}

const formatMemory = (mb: number, unit: string): string => {
    if (unit === 'GB') {
        return `${(mb / 1024).toFixed(mb % 1024 === 0 ? 0 : 1)} GB`;
    }
    return `${mb} MB`;
};

const panelClass = 'bg-gray-700 backdrop border border-gray-500 rounded-box';
const fieldPanelClass = `${panelClass} p-6`;
const statChipClass = 'bg-gray-600 border border-gray-500 rounded-component px-4 py-3';
const selectedCardClass = 'border-arix bg-gray-600 shadow-lg';
const idleCardClass = 'border-gray-500 bg-gray-600 hover:border-gray-400 hover:bg-gray-500';
const resourceChipClass = 'inline-flex items-center gap-1 text-xs bg-gray-700 border border-gray-500 text-gray-200 px-2 py-1 rounded-component';
const dividerClass = 'border-gray-500';

const FreeServersPageContent = () => {
    const { t } = useLanguage();
    const history = useHistory();
    const [data, setData] = useState<FreeServersData | null>(null);
    const [loading, setLoading] = useState(true);
    const [creating, setCreating] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [success, setSuccess] = useState<string | null>(null);

    const [serverName, setServerName] = useState('');
    const [selectedEgg, setSelectedEgg] = useState<number | null>(null);
    const [selectedNode, setSelectedNode] = useState<number | null>(null);

    const fetchData = () => {
        setLoading(true);
        http.get('/api/client/extensions/freeservers')
            .then((response) => {
                setData(response.data);
                if (response.data.nodes?.length === 1) {
                    setSelectedNode(response.data.nodes[0].id);
                }
            })
            .catch(() => {
                setError(t('loadingError'));
            })
            .finally(() => {
                setLoading(false);
            });
    };

    useEffect(() => {
        fetchData();
    }, []);

    const handleCreate = async () => {
        if (!serverName || !selectedEgg || !selectedNode) {
            setError(t('fillAllFields'));
            return;
        }

        setCreating(true);
        setError(null);
        setSuccess(null);

        try {
            const response = await http.post('/api/client/extensions/freeservers/create', {
                name: serverName,
                egg_id: selectedEgg,
                node_id: selectedNode,
            });

            if (response.data.success) {
                setSuccess(t('serverCreatedSuccess'));
                fetchData();
                setTimeout(() => {
                    history.push('/');
                }, 2000);
            } else {
                setError(response.data.message || t('errorCreatingServer'));
            }
        } catch (err: any) {
            setError(err.response?.data?.message || t('errorCreatingServer'));
        } finally {
            setCreating(false);
        }
    };

    const selectedEggData = data?.eggs.find(e => e.id === selectedEgg);

    if (loading) {
        return (
            <PageContentBlock title={t('pageTitle')}>
                <div className={`${panelClass} flex justify-center items-center h-64`}>
                    <Spinner size="large" />
                </div>
            </PageContentBlock>
        );
    }

    if (!data || !data.enabled || data.max_servers === 0) {
        return (
            <PageContentBlock title={t('pageTitle')}>
                <div className={`${panelClass} p-8 text-center`}>
                    <FontAwesomeIcon icon={faExclamationCircle} className="w-16 h-16 mx-auto text-yellow-500 mb-4" />
                    <h2 className="text-xl font-semibold text-gray-50 mb-2">{t('unavailableTitle')}</h2>
                    <p className="text-gray-300">
                        {data?.message || t('unavailableDefault')}
                    </p>
                </div>
            </PageContentBlock>
        );
    }

    return (
        <PageContentBlock title={t('pageTitle')}>
            {/* Header Section */}
            <div className={`${panelClass} relative overflow-hidden p-6 mb-6`}>
                <div className="absolute inset-x-0 top-0 h-px bg-arix" />
                <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                    <div className="flex items-center gap-4">
                        <div className="bg-gray-600 border border-arix rounded-component p-4 text-arix">
                            <FontAwesomeIcon icon={faServer} className="w-8 h-8" />
                        </div>
                        <div>
                            <h1 className="text-2xl font-bold text-gray-50">{t('pageTitle')}</h1>
                            <p className="text-gray-300">
                                {t('createUpTo').replace('{max}', String(data.max_servers))}
                            </p>
                        </div>
                    </div>
                    <div className="grid grid-cols-2 gap-3">
                        <div className={statChipClass}>
                            <span className="block text-gray-300 text-xs uppercase tracking-wide">{t('serversUsed')}</span>
                            <span className="block text-gray-50 font-semibold text-lg">{data.current_servers} / {data.max_servers}</span>
                        </div>
                        <div className={statChipClass}>
                            <span className="block text-gray-300 text-xs uppercase tracking-wide">{t('available')}</span>
                            <span className="block text-arix font-semibold text-lg">{data.remaining}</span>
                        </div>
                    </div>
                </div>
            </div>

            {/* Error/Success Messages */}
            {error && (
                <div className="bg-danger-200 border border-danger-100 text-danger-50 rounded-component p-4 mb-6">
                    <FontAwesomeIcon icon={faExclamationCircle} className="mr-2" />
                    {error}
                </div>
            )}
            {success && (
                <div className="bg-success-200 border border-success-100 text-success-50 rounded-component p-4 mb-6">
                    <FontAwesomeIcon icon={faCheckCircle} className="mr-2" />
                    {success}
                </div>
            )}

            {!data.can_create ? (
                <div className={`${panelClass} p-8 text-center`}>
                    <FontAwesomeIcon icon={faExclamationCircle} className="w-16 h-16 mx-auto text-yellow-500 mb-4" />
                    <h2 className="text-xl font-semibold text-gray-50 mb-2">{t('limitReachedTitle')}</h2>
                    <p className="text-gray-300">
                        {t('limitReachedDesc')}
                    </p>
                </div>
            ) : (
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Main Form */}
                    <div className="lg:col-span-2 space-y-6">
                        {/* Server Name */}
                        <div className={fieldPanelClass}>
                            <h3 className="text-lg font-semibold text-gray-50 mb-4 flex items-center gap-2">
                                <FontAwesomeIcon icon={faServer} className="w-5 h-5 text-arix" />
                                {t('serverName')}
                            </h3>
                            <input
                                type="text"
                                value={serverName}
                                onChange={(e) => setServerName(e.target.value)}
                                placeholder={t('serverNamePlaceholder')}
                                className="w-full bg-gray-800 border border-gray-500 rounded-component px-4 py-3 text-gray-50 placeholder-gray-400 focus:border-arix focus:outline-none transition-colors"
                                maxLength={40}
                                minLength={3}
                            />
                            <p className="text-gray-300 text-sm mt-2">
                                {t('charRange')}
                            </p>
                        </div>

                        {/* Egg Selection */}
                        <div className={fieldPanelClass}>
                            <h3 className="text-lg font-semibold text-gray-50 mb-4 flex items-center gap-2">
                                <FontAwesomeIcon icon={faLayerGroup} className="w-5 h-5 text-arix" />
                                {t('selectEgg')}
                            </h3>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {data.eggs.map((egg) => (
                                    <button
                                        key={egg.id}
                                        aria-pressed={selectedEgg === egg.id}
                                        onClick={() => setSelectedEgg(egg.id)}
                                        className={`p-4 rounded-component border transition-all text-left focus:outline-none focus:border-arix ${
                                            selectedEgg === egg.id
                                                ? selectedCardClass
                                                : idleCardClass
                                        }`}
                                    >
                                        <div className="flex items-start justify-between">
                                            <div>
                                                <h4 className="font-semibold text-gray-50">{egg.name}</h4>
                                                <p className="text-gray-300 text-sm mt-1">{egg.nest_name}</p>
                                            </div>
                                            {selectedEgg === egg.id && (
                                                <FontAwesomeIcon icon={faCheckCircle} className="w-5 h-5 text-arix" />
                                            )}
                                        </div>
                                        {egg.description && (
                                            <p className="text-gray-300 text-sm mt-2 line-clamp-2">
                                                {egg.description}
                                            </p>
                                        )}
                                        <div className="flex flex-wrap gap-2 mt-3">
                                            <span className={resourceChipClass}>
                                                <FontAwesomeIcon icon={faMemory} className="w-3 h-3" />
                                                {formatMemory(egg.memory, data.display_unit)}
                                            </span>
                                            <span className={resourceChipClass}>
                                                <FontAwesomeIcon icon={faHdd} className="w-3 h-3" />
                                                {formatMemory(egg.disk, data.display_unit)}
                                            </span>
                                            <span className={resourceChipClass}>
                                                <FontAwesomeIcon icon={faMicrochip} className="w-3 h-3" />
                                                {egg.cpu}%
                                            </span>
                                        </div>
                                    </button>
                                ))}
                            </div>
                        </div>

                        {/* Node Selection */}
                        <div className={fieldPanelClass}>
                            <h3 className="text-lg font-semibold text-gray-50 mb-4 flex items-center gap-2">
                                <FontAwesomeIcon icon={faServer} className="w-5 h-5 text-arix" />
                                {t('selectNode')}
                            </h3>
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                {data.nodes.map((node) => (
                                    <button
                                        key={node.id}
                                        aria-pressed={selectedNode === node.id}
                                        onClick={() => setSelectedNode(node.id)}
                                        className={`p-4 rounded-component border transition-all focus:outline-none focus:border-arix ${
                                            selectedNode === node.id
                                                ? selectedCardClass
                                                : idleCardClass
                                        }`}
                                    >
                                        <div className="flex items-center justify-between">
                                            <div>
                                                <h4 className="font-semibold text-gray-50">{node.name}</h4>
                                                <p className="text-gray-300 text-sm">{node.location}</p>
                                            </div>
                                            {selectedNode === node.id && (
                                                <FontAwesomeIcon icon={faCheckCircle} className="w-5 h-5 text-arix" />
                                            )}
                                        </div>
                                    </button>
                                ))}
                            </div>
                        </div>
                    </div>

                    {/* Summary & Create Button */}
                    <div className="lg:col-span-1">
                        <div className={`${panelClass} p-6 sticky top-6`}>
                            <h3 className="text-lg font-semibold text-gray-50 mb-4">{t('summaryTitle')}</h3>
                            
                            <div className="space-y-3 mb-6">
                                <div className="flex justify-between text-sm">
                                    <span className="text-gray-300">{t('summaryServerName')}:</span>
                                    <span className="text-gray-50 text-right">{serverName || '-'}</span>
                                </div>
                                <div className="flex justify-between text-sm">
                                    <span className="text-gray-300">{t('summaryServerType')}:</span>
                                    <span className="text-gray-50 text-right">{selectedEggData?.name || '-'}</span>
                                </div>
                                <div className="flex justify-between text-sm">
                                    <span className="text-gray-300">{t('summaryLocation')}:</span>
                                    <span className="text-gray-50 text-right">
                                        {data.nodes.find(n => n.id === selectedNode)?.name || '-'}
                                    </span>
                                </div>
                                
                                {selectedEggData && (
                                    <>
                                        <hr className={dividerClass} />
                                        <div className="flex justify-between text-sm">
                                            <span className="text-gray-300">{t('summaryRam')}:</span>
                                            <span className="text-gray-50">{formatMemory(selectedEggData.memory, data.display_unit)}</span>
                                        </div>
                                        <div className="flex justify-between text-sm">
                                            <span className="text-gray-300">{t('summaryStorage')}:</span>
                                            <span className="text-gray-50">{formatMemory(selectedEggData.disk, data.display_unit)}</span>
                                        </div>
                                        <div className="flex justify-between text-sm">
                                            <span className="text-gray-300">{t('summaryCpu')}:</span>
                                            <span className="text-gray-50">{selectedEggData.cpu}%</span>
                                        </div>
                                    </>
                                )}

                                <hr className={dividerClass} />
                                <div className="flex justify-between text-sm font-semibold">
                                    <span className="text-gray-300">{t('summaryPrice')}:</span>
                                    <span className="text-arix">{t('priceFree')}</span>
                                </div>
                            </div>

                            <button
                                onClick={handleCreate}
                                disabled={creating || !serverName || !selectedEgg || !selectedNode}
                                className="w-full bg-arix hover:opacity-90 disabled:bg-gray-500 disabled:text-gray-300 disabled:cursor-not-allowed text-gray-900 font-semibold py-3 px-4 rounded-component transition-all flex items-center justify-center gap-2"
                            >
                                {creating ? (
                                    <>
                                        <Spinner size="small" />
                                        {t('creating')}
                                    </>
                                ) : (
                                    <>
                                        {t('createButton')}
                                        <FontAwesomeIcon icon={faChevronRight} className="w-5 h-5" />
                                    </>
                                )}
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </PageContentBlock>
    );
};


export default FreeServersPageContent;
