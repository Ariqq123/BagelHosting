import React, { useEffect, useState } from 'react';
import { Server } from '@/api/server/getServer';
import { ApplicationStore } from '@/state';
import getServers from '@/api/getServers';
import ServerCard from '@/components/dashboard/ServerCard';
import ServerCardBanner from '@/components/dashboard/ServerCardBanner';
import ServerCardGradient from '@/components/dashboard/ServerCardGradient';
import Spinner from '@/components/elements/Spinner';
import PageContentBlock from '@/components/elements/PageContentBlock';
import useFlash from '@/plugins/useFlash';
import { useStoreState } from 'easy-peasy';
import { usePersistedState } from '@/plugins/usePersistedState';
import Switch from '@/components/elements/Switch';
import tw from 'twin.macro';
import useSWR from 'swr';
import { LuChevronRight, LuCreditCard, LuLifeBuoy, LuRouter } from "react-icons/lu";
import { RxDiscordLogo } from "react-icons/rx";
import { FaDiscord } from "react-icons/fa";
import { PaginatedResult } from '@/api/http';
import Pagination from '@/components/elements/Pagination';
import { useLocation } from 'react-router-dom';
import { useTranslation } from 'react-i18next';

export default () => {
    const { t } = useTranslation('arix/dashboard');
    const { search } = useLocation();
    const defaultPage = Number(new URLSearchParams(search).get('page') || '1');
    const [guildData, setGuildData] = useState<{ instant_invite: string, presence_count: number } | null>(null);

    const [page, setPage] = useState(!isNaN(defaultPage) && defaultPage > 0 ? defaultPage : 1);
    const { clearFlashes, clearAndAddHttpError } = useFlash();
    const uuid = useStoreState((state) => state.user.data!.uuid);
    const rootAdmin = useStoreState((state) => state.user.data!.rootAdmin);
    const [showOnlyAdmin, setShowOnlyAdmin] = usePersistedState(`${uuid}:show_all_servers`, false);
    const discordBox = useStoreState((state: ApplicationStore) => state.settings.data!.arix.discordBox);
    const discord = useStoreState((state: ApplicationStore) => state.settings.data!.arix.discord);
    const billing = useStoreState((state: ApplicationStore) => state.settings.data!.arix.billing);
    const support = useStoreState((state: ApplicationStore) => state.settings.data!.arix.support);
    const status = useStoreState((state: ApplicationStore) => state.settings.data!.arix.status);
    const socialButtons = useStoreState((state: ApplicationStore) => state.settings.data!.arix.socialButtons);
    const serverRow = useStoreState((state: ApplicationStore) => state.settings.data!.arix.serverRow);

    const { data: servers, error } = useSWR<PaginatedResult<Server>>(
        ['/api/client/servers', showOnlyAdmin && rootAdmin, page],
        () => getServers({ page, type: showOnlyAdmin && rootAdmin ? 'admin' : undefined })
    );

    useEffect(() => {
        if (!servers) return;
        if (servers.pagination.currentPage > 1 && !servers.items.length) {
            setPage(1);
        }
    }, [servers?.pagination.currentPage]);

    useEffect(() => {
        // Don't use react-router to handle changing this part of the URL, otherwise it
        // triggers a needless re-render. We just want to track this in the URL incase the
        // user refreshes the page.
        window.history.replaceState(null, document.title, `/${page <= 1 ? '' : `?page=${page}`}`);
    }, [page]);

    useEffect(() => {
        if (error) clearAndAddHttpError({ key: 'dashboard', error });
        if (!error) clearFlashes('dashboard');
    }, [error]);

    useEffect(() => {
        const fetchData = async () => {
            try {
                const response = await fetch(`https://discord.com/api/guilds/${discord}/widget.json`);

                if (!response.ok) {
                    throw new Error('Failed to fetch guild data');
                }

            const data = await response.json();
                setGuildData(data);
            } catch (error) {
                console.error('Error fetching guild data:', error);
            }
        };

        fetchData();
    }, []);

    return (
        <PageContentBlock title={'Dashboard'} showFlashKey={'dashboard'}>
            {String(socialButtons) == 'true' &&
            <div className={'flex lg:gap-4 gap-2 lg:flex-row flex-col mb-4'}>
                {discord &&
                    <a href={guildData ? guildData.instant_invite : ''} target="_blank" className={'group w-full bg-gray-700 backdrop rounded-box flex items-center justify-between px-6 py-5'}>
                        <div>
                            <p className={'font-medium text-gray-100 flex items-center'}>
                                Discord
                                <LuChevronRight className={'opacity-0 ml-0 group-hover:opacity-75 group-hover:ml-2 duration-300'} />
                            </p>
                            <span className={'font-light text-sm text-gray-200'}>{t('join-our-discord')}</span>
                        </div>
                        <RxDiscordLogo className={'text-[2.5rem] text-arix'}/>
                    </a>
                }
                {billing &&
                    <a href={billing} target="_blank" className={'group w-full bg-gray-700 backdrop rounded-box flex items-center justify-between px-6 py-5'}>
                        <div>
                            <p className={'font-medium text-gray-100 flex items-center'}>
                                {t('billing-area')}
                                <LuChevronRight className={'opacity-0 ml-0 group-hover:opacity-75 group-hover:ml-2 duration-300'} />
                            </p>
                            <span className={'font-light text-sm text-gray-200'}>{t('manage-your-services')}</span>
                        </div>
                        <LuCreditCard className={'text-[2.5rem] text-arix'}/>
                    </a>
                }
                {support &&
                    <a href={support} target="_blank" className={'group w-full bg-gray-700 backdrop rounded-box flex items-center justify-between px-6 py-5'}>
                        <div>
                            <p className={'font-medium text-gray-100 flex items-center'}>
                                {t('supportcenter')}
                                <LuChevronRight className={'opacity-0 ml-0 group-hover:opacity-75 group-hover:ml-2 duration-300'} />
                            </p>
                            <span className={'font-light text-sm text-gray-200'}>{t('get-support')}</span>
                        </div>
                        <LuLifeBuoy className={'text-[2.5rem] text-arix'}/>
                    </a>
                }
                {status &&
                    <a href={status} target="_blank" className={'group w-full bg-gray-700 backdrop rounded-box flex items-center justify-between px-6 py-5'}>
                        <div>
                            <p className={'font-medium text-whgray-100ite flex items-center'}>
                                {t('server-status')}
                                <LuChevronRight className={'opacity-0 ml-0 group-hover:opacity-75 group-hover:ml-2 duration-300'} />
                            </p>
                            <span className={'font-light text-sm text-gray-200'}>{t('check-server-status')}</span>
                        </div>
                        <LuRouter className={'text-[2.5rem] text-arix'}/>
                    </a>
                }
            </div>}
            <div className={'flex gap-4 md:flex-nowrap flex-wrap mb-6'}>
                <div className={'bg-gray-700 backdrop rounded-box px-6 py-5 w-full flex items-center justify-between'}>
                    <div>
                        <p className={'text-gray-50'}>{t('welcome-back')}</p>
                        <p className={'font-light'}>{t('all-servers-you-have-access-to')}</p>
                    </div>
                    {rootAdmin && (
                        <div css={tw`flex justify-end items-center`}>
                            <p css={tw`uppercase text-xs text-neutral-400 mr-2`}>
                                {showOnlyAdmin ? t('others-servers') : t('your-servers')}
                            </p>
                            <Switch
                                name={'show_all_servers'}
                                defaultChecked={showOnlyAdmin}
                                onChange={() => setShowOnlyAdmin((s) => !s)}
                            />
                        </div>
                    )}
                </div>
                {String(discordBox) == 'true' &&
                <a href={guildData ? guildData.instant_invite : ''} target="_blank" className={'group lg:max-w-[275px] w-full border border-[#6374AC] hover:border-[#97A8E0] rounded-box flex items-center justify-between px-6 py-5 duration-300'} css={'background-image:radial-gradient(circle, rgba(27,43,104,1) 0%, rgba(9,39,78,1) 100%);'}>
                    <div>
                        <span className={'font-light text-sm text-white/70'}>{guildData ? guildData.presence_count : '000'} {t('members-online')}</span>
                        <p className={'font-medium text-white'}>{t('join-our-discord')}</p>
                    </div>
                    <FaDiscord className={'text-[2.5rem] text-white/70 group-hover:text-white duration-300'}/>
                </a>}
            </div>
            {!servers ? (
                <Spinner centered size={'large'} />
            ) : (
                <div className="grid lg:grid-cols-2 gap-4">
                    <Pagination data={servers} onPageSelect={setPage}>
                            {({ items }) =>
                                items.length > 0 ? (
                                    items.map((server, index) => (
                                        serverRow == 1 
                                            ? <ServerCardGradient key={server.uuid} server={server} css={index > 0 ? tw`mt-2` : undefined} />
                                            : serverRow == 2
                                            ? <ServerCardBanner key={server.uuid} server={server} css={index > 0 ? tw`mt-2` : undefined} />
                                            : serverRow == 3
                                            && <ServerCard key={server.uuid} server={server} css={index > 0 ? tw`mt-2` : undefined} />
                                    ))
                                ) : (
                                    <p css={tw`text-center text-sm text-neutral-400 lg:col-span-2 col-span-1`}>
                                        {showOnlyAdmin
                                            ? t('there-are-no-servers')
                                            : t('there-are-no-servers-associated')}
                                    </p>
                                )
                            }
                    </Pagination>
                </div>
            )}
        </PageContentBlock>
    );
};
