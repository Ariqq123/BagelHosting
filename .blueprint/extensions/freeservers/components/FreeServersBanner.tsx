import React from 'react';
import { Link } from 'react-router-dom';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faServer, faChevronRight } from '@fortawesome/free-solid-svg-icons';
import { useLanguage } from './i18n/LanguageContext';

interface FreeServersData {
    enabled: boolean;
    remaining: number;
    max_servers: number;
    current_servers: number;
    language?: string;
}

interface Props {
    data: FreeServersData;
}

const FreeServersBanner: React.FC<Props> = ({ data }) => {
    const { t } = useLanguage();

    const bannerMsg = t('bannerMessage')
        .replace('{remaining}', String(data.remaining))
        .replace('{max}', String(data.max_servers));

    return (
        <div className="mb-6">
            <Link
                to="/account/freeservers"
                className="block bg-gray-700 backdrop border border-gray-500 rounded-box p-4 transition-all hover:border-arix focus:border-arix focus:outline-none"
            >
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div className="flex items-center gap-4">
                        <div className="bg-gray-600 border border-arix rounded-component p-3 text-arix">
                            <FontAwesomeIcon icon={faServer} className="w-6 h-6" />
                        </div>
                        <div className="min-w-0">
                            <h3 className="text-gray-50 font-semibold text-lg">
                                {t('bannerTitle')}
                            </h3>
                            <p className="text-gray-300 text-sm">
                                {bannerMsg}
                            </p>
                        </div>
                    </div>
                    <div className="flex items-center gap-2 text-arix sm:justify-end">
                        <span className="text-sm font-medium">{t('bannerButton')}</span>
                        <FontAwesomeIcon icon={faChevronRight} className="w-5 h-5" />
                    </div>
                </div>
            </Link>
        </div>
    );
};

export default FreeServersBanner;
