import React, { useEffect, useState } from 'react';
import http from '@/api/http';
import Spinner from '@/components/elements/Spinner';
import PageContentBlock from '@/components/elements/PageContentBlock';
import { LanguageProvider } from './i18n/LanguageContext';
import { Language } from './i18n/translations';
import FreeServersPageContent from './FreeServersPageContent';

interface InitialData {
    language?: string;
}

export default () => {
    const [language, setLanguage] = useState<Language>('en');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        // Fetch language from API settings
        http.get<InitialData>('/api/client/extensions/freeservers')
            .then((response) => {
                const lang = response.data.language;
                const validLanguages = ['en', 'de', 'fr', 'es', 'it', 'pt', 'nl', 'pl', 'cs', 'ro', 'sv', 'hu', 'el', 'da', 'fi', 'no', 'tr'];
                if (lang && validLanguages.includes(lang)) {
                    setLanguage(lang as Language);
                }
            })
            .catch(() => {
                // Fallback to 'en' on error
            })
            .finally(() => {
                setLoading(false);
            });
    }, []);

    if (loading) {
        return (
            <PageContentBlock title="Free Servers">
                <div className="flex justify-center items-center h-64">
                    <Spinner size="large" />
                </div>
            </PageContentBlock>
        );
    }

    return (
        <LanguageProvider initialLanguage={language}>
            <FreeServersPageContent />
        </LanguageProvider>
    );
};
