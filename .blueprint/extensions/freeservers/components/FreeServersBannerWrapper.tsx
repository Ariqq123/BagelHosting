import React, { useEffect, useState } from 'react';
import http from '@/api/http';
import { LanguageProvider } from './i18n/LanguageContext';
import { Language } from './i18n/translations';
import FreeServersBanner from './FreeServersBanner';

interface FreeServersData {
    enabled: boolean;
    remaining: number;
    max_servers: number;
    current_servers: number;
    language?: string;
    message?: string;
}

export default () => {
    const [language, setLanguage] = useState<Language>('en');
    const [data, setData] = useState<FreeServersData | null>(null);
    const [ready, setReady] = useState(false);

    const validLanguages = ['en', 'de', 'fr', 'es', 'it', 'pt', 'nl', 'pl', 'cs', 'ro', 'sv', 'hu', 'el', 'da', 'fi', 'no', 'tr'];

    useEffect(() => {
        // Single fetch: get data + language together, then render banner
        http.get('/api/client/extensions/freeservers')
            .then((response) => {
                const lang = response.data.language;
                if (lang && validLanguages.includes(lang)) {
                    setLanguage(lang as Language);
                }
                setData(response.data);
            })
            .catch(() => {
                // Silent fail
            })
            .finally(() => {
                setReady(true);
            });
    }, []);

    // Don't render until we have the correct language
    if (!ready || !data || !data.enabled || data.remaining <= 0) {
        return null;
    }

    return (
        <LanguageProvider initialLanguage={language}>
            <FreeServersBanner data={data} />
        </LanguageProvider>
    );
};
