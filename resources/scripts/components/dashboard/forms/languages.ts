export const languages = [
    { key: 'en', value: 'English' },
    { key: 'cs', value: 'Czech' },
    { key: 'da', value: 'Danish' },
    { key: 'de', value: 'German' },
    { key: 'es', value: 'Spanish' },
    { key: 'fr', value: 'French' },
    { key: 'hi', value: 'Hindi' },
    { key: 'hi-EN', value: 'Hindi (English)' },
    { key: 'id', value: 'Indonesian' },
    { key: 'it', value: 'Italian' },
    { key: 'ja', value: 'Japanese' },
    { key: 'ko', value: 'Korean' },
    { key: 'lt', value: 'Lithuanian' },
    { key: 'nl', value: 'Dutch' },
    { key: 'pa', value: 'Punjabi' },
    { key: 'pt', value: 'Portuguese' },
    { key: 'pt-BR', value: 'Portuguese (Brazil)' },
    { key: 'ro', value: 'Romanian' },
    { key: 'ru', value: 'Russian' },
    { key: 'sv', value: 'Swedish' },
    { key: 'tr', value: 'Turkish' },
    { key: 'uk', value: 'Ukrainian' },
    { key: 'ur', value: 'Urdu' },
];

export const supportedLanguageKeys = languages.map((language) => language.key);

const findSupportedLanguage = (language: string): string | undefined => {
    return supportedLanguageKeys.find(
        (supportedLanguage) => supportedLanguage.toLowerCase() === language.toLowerCase()
    );
};

export const normalizeLanguage = (language?: string): string => {
    if (!language) {
        return 'en';
    }

    const supportedLanguage = findSupportedLanguage(language);

    if (supportedLanguage) {
        return supportedLanguage;
    }

    const baseLanguage = language.split('-')[0];

    return findSupportedLanguage(baseLanguage) || 'en';
};
