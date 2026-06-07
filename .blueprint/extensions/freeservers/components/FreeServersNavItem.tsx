import React from 'react';
import { NavLink } from 'react-router-dom';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faGift } from '@fortawesome/free-solid-svg-icons';
import { useLanguage } from './i18n/LanguageContext';

export default () => {
    const { t } = useLanguage();
    
    return (
        <NavLink to="/account/freeservers" className="flex items-center gap-2 px-4 py-2 text-neutral-300 hover:text-neutral-100 hover:bg-neutral-700 rounded transition-colors">
            <FontAwesomeIcon icon={faGift} className="w-5 h-5" />
            <span>{t('navTitle')}</span>
        </NavLink>
    );
};
