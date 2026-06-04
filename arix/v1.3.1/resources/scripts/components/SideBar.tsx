import React, { useRef, useState, useEffect } from 'react';
import { NavLink, Link } from 'react-router-dom';
import { useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import DropdownMenu, { DropdownLinkRow, DropdownButtonRow } from '@/components/elements/DropdownMenu';
import SpinnerOverlay from '@/components/elements/SpinnerOverlay';
import UserAvatar from '@/components/UserAvatar';
import { ServerIcon, UserCircleIcon, DotsVerticalIcon, CogIcon, EyeIcon, MoonIcon, LogoutIcon } from '@heroicons/react/outline';
import http from '@/api/http';
import styled from 'styled-components/macro';
import tw from 'twin.macro';
import { useTranslation } from 'react-i18next';

interface Props {
    children?: React.ReactNode;
    type?: boolean;
}

const SideBar = ({ children, type }: Props) => {
    const { t } = useTranslation('arix/navigation');
    const [isDarkMode, setIsDarkMode] = useState<boolean>(false);
    const onClickRef = useRef<DropdownMenu>(null);
    const [isLoggingOut, setIsLoggingOut] = useState(false);
    const name = useStoreState((state: ApplicationStore) => state.settings.data!.name);
    const layout = useStoreState((state: ApplicationStore) => state.settings.data!.arix.layout);
    const modeToggler = useStoreState((state: ApplicationStore) => state.settings.data!.arix.modeToggler);
    const logo = useStoreState((state: ApplicationStore) => state.settings.data!.arix.logo);
    const logoHeight = useStoreState((state: ApplicationStore) => state.settings.data!.arix.logoHeight);
    const fullLogo = useStoreState((state: ApplicationStore) => state.settings.data!.arix.fullLogo);
    const rootAdmin = useStoreState((state: ApplicationStore) => state.user.data!.rootAdmin);

    useEffect(() => {
        const storedMode = localStorage.getItem('darkMode');
        if (storedMode !== null) {
          setIsDarkMode(storedMode === 'true');
        }
    }, []);

    useEffect(() => {
        localStorage.setItem('darkMode', String(isDarkMode));
        document.body.classList.toggle('lightmode', isDarkMode);
    }, [isDarkMode]);

    const toggleDarkMode = () => {
        setIsDarkMode((prevMode) => !prevMode);
    };

    const onTriggerLogout = () => {
        setIsLoggingOut(true);
        http.post('/auth/logout').finally(() => {
            // @ts-expect-error this is valid
            window.location = '/';
        });
    };

    const NavigationLinks = styled.div`
        ${tw`flex flex-col gap-1`};

        & > div{
            ${tw`flex flex-col gap-1`};

            & > span{
                ${tw`px-5 text-sm text-gray-300 font-medium mt-4 uppercase`}
            }
        }

        & a{
            ${tw`relative z-10 flex px-5 py-2 gap-x-2 items-center duration-300 border-r-2 border-transparent`}

            & > svg{
                ${tw`text-gray-300 w-5`}
            }

            &::after{
                ${tw`absolute inset-0 z-[-1] opacity-0 duration-300`}
                content: '';
                background: ${layout == 5 ? 'var(--primary)' : 'linear-gradient(90deg, color-mix(in srgb, var(--gray700) 0%, transparent) 0%, color-mix(in srgb, var(--primary) 30%, transparent) 100%)'}; 
            }

            &:hover,
            &:focus,
            &.active{
                ${tw`text-gray-100 border-arix`}

                &::after{
                    ${tw`opacity-100`}
                }

                & > svg{
                    ${layout == 5 ? tw`text-gray-100` : tw`text-arix duration-300`}
                }
            }
        }
    `;

    return (
    <div className={'w-[250px] shrink-0 bg-gray-700 h-screen overflow-y-auto lg:flex hidden flex-col sticky top-0 backdrop border-t-0 border-b-0 border-l-0'}>
        <SpinnerOverlay visible={isLoggingOut} />
        <div className={'pt-3'}>
            <Link to={'/'} className='flex gap-x-2 items-center font-semibold text-lg text-gray-50 px-5 pt-2 pb-5'>
                <img src={logo} alt={name + 'logo'} css={`height:${logoHeight};`} />
                {String(fullLogo) === 'false' && name}
            </Link>
            {!type &&
            <NavigationLinks className={'mb-4'}>
                <NavLink to={'/'} exact>
                    <ServerIcon/> {t('servers')}
                </NavLink>
                <NavLink to={'/account'} exact>
                    <UserCircleIcon/> {t('account')}
                </NavLink>
            </NavigationLinks>}
            <hr className={'border-b border-gray-500 mx-5'}/>
        </div>
        {children &&
        <NavigationLinks className={'pb-2'}>
            {children}
        </NavigationLinks>
        }
        <div className="sticky bottom-0 bg-gray-700 pb-4 px-5 z-20 mt-auto backdrop-blur-xl">
            <hr className={'border-b border-gray-500 mb-4'}/>
            <div className="flex w-full justify-between items-center">
                <Link to="/account" className="flex items-center gap-x-2">
                    <UserAvatar /> 
                    <div>
                        <p>{t('account')}</p>
                    </div>
                </Link>
                <DropdownMenu
                    ref={onClickRef}
                    sideBar
                    renderToggle={(onClick) => (
                        <div onClick={onClick} className="cursor-pointer text-gray-50 p-2">
                            <DotsVerticalIcon className="w-5" />
                        </div>
                    )}
                >
                    {rootAdmin && <DropdownLinkRow href="/admin">
                        <CogIcon className="w-5" /> {t('admin-view')}
                    </DropdownLinkRow> }
                    <DropdownLinkRow href="/account/activity">
                        <EyeIcon className="w-5" /> {t('account-activity')}
                    </DropdownLinkRow>
                    {String(modeToggler) == 'true' &&
                    <DropdownButtonRow onClick={toggleDarkMode}>
                        <MoonIcon className="w-5" /> {t('dark-light-mode')}
                    </DropdownButtonRow>}
                    <hr className={'border-b border-gray-500 my-2'}/>
                    <DropdownButtonRow danger onClick={onTriggerLogout}>
                        <LogoutIcon className="w-5" /> {t('logout')}
                    </DropdownButtonRow>
                </DropdownMenu>
            </div>
        </div>
    </div>
)};

export default SideBar;