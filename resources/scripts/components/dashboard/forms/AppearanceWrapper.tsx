import React, { useState, useEffect, ChangeEvent } from 'react';
import { useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import { useTranslation } from 'react-i18next';
import TitledGreyBox from '@/components/elements/TitledGreyBox';
import Switch from '@/components/elements/Switch';
import Select from '@/components/elements/Select';

const AppearanceWrapper = () => {
    const { i18n, t } = useTranslation('arix/account');
    const [selectedLanguage, setSelectedLanguage] = useState(i18n.language);
    const [isDarkMode, setIsDarkMode] = useState<boolean>(() => {
        const storedMode = localStorage.getItem('darkMode');
        return storedMode ? storedMode === 'true' : false;
    });
    const [panelSounds, isPanelSounds] = useState<boolean>(() => {
        const soundsEnabled = localStorage.getItem('panelSounds');
        return soundsEnabled ? soundsEnabled === 'true' : false;
    });

    const modeToggler = String(useStoreState((state: ApplicationStore) => state.settings.data!.arix.modeToggler));
    const langSwitch = String(useStoreState((state: ApplicationStore) => state.settings.data!.arix.langSwitch));
    const defaultMode = useStoreState((state: ApplicationStore) => state.settings.data!.arix.defaultMode);

    const languages = [
        { key: 'en', value: 'English' },
    ];

    const handleLanguageChange = (event: ChangeEvent<HTMLSelectElement>) => {
        const newLanguage = event.target.value;
        i18n.changeLanguage(newLanguage);
        setSelectedLanguage(newLanguage);
    };

    useEffect(() => {
        setSelectedLanguage(i18n.language || 'en');
    }, [i18n.language]);
    

    useEffect(() => {
        localStorage.setItem('darkMode', String(isDarkMode));
        document.body.classList.toggle('lightmode', isDarkMode);
    }, [isDarkMode]);

    const toggleDarkMode = () => {
        setIsDarkMode((prevMode) => !prevMode);
    };

    useEffect(() => {
        localStorage.setItem('panelSounds', String(panelSounds));
    }, [panelSounds]);
    
    const toggleSounds = () => {
        isPanelSounds((prevMode) => !prevMode);
    };

    return (
        <TitledGreyBox title={t('appearance.title')}>
            {modeToggler == 'true' &&
            <div className={'flex justify-between items-center mb-4'}>
                <p>{t('appearance.lightDarkMode')}</p>
                <div className={'flex gap-x-2 items-center'}>
                    <span className={'text-sm text-gray-300'}>{defaultMode == 'lightmode' ? t('appearance.light') : t('appearance.dark')}</span>
                    <Switch name={'mode'} onChange={toggleDarkMode} defaultChecked={isDarkMode} />
                    <span className={'text-sm text-gray-300'}>{defaultMode !== 'lightmode' ? t('appearance.light') : t('appearance.dark')}</span>
                </div>
            </div>}
            {langSwitch == 'true' &&
            <div className={'flex justify-between items-center mb-4'}>
                <p className={'flex-1'}>{t('appearance.language')}</p>
                <Select value={selectedLanguage} className={'!w-auto !pr-10'} onChange={handleLanguageChange}>
                    {languages.map(({ key, value }) => (
                        <option key={key} value={key}>
                            {value}
                        </option>
                    ))}
                </Select>
            </div>}
            <div className={'flex justify-between items-center'}>
                <p>{t('appearance.panel-sounds')}</p>
                <div className={'flex gap-x-2 items-center'}>
                    <span className={'text-sm text-gray-300'}>{t('appearance.off')}</span>
                    <Switch name={'mode'} onChange={toggleSounds} defaultChecked={panelSounds} />
                    <span className={'text-sm text-gray-300'}>{t('appearance.on')}</span>
                </div>
            </div>
        </TitledGreyBox>
    );
};

export default AppearanceWrapper;
