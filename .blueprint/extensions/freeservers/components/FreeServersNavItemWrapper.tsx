import React, { useEffect, useState } from 'react';
import http from '@/api/http';
import { LanguageProvider } from './i18n/LanguageContext';
import { Language } from './i18n/translations';
import FreeServersNavItem from './FreeServersNavItem';

interface Settings {
    language?: string;
}

export default () => {
    const [language, setLanguage] = useState<Language>('en');

    useEffect(() => {
        // Fetch language from API settings
        http.get<Settings>('/api/client/extensions/freeservers')
            .then((response) => {
                const lang = response.data.language;
                const validLanguages = ['en', 'de', 'fr', 'es', 'it', 'pt', 'nl', 'pl', 'cs', 'ro', 'sv', 'hu', 'el', 'da', 'fi', 'no', 'tr'];
                if (lang && validLanguages.includes(lang)) {
                    setLanguage(lang as Language);
                }
            })
            .catch(() => {
                // Fallback to 'en' on error
            });
    }, []);

    return (
        <LanguageProvider initialLanguage={language}>
            <FreeServersNavItem />
        </LanguageProvider>
    );
};
