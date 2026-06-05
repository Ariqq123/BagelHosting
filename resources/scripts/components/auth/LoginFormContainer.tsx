import React, { forwardRef, useEffect, useState } from 'react';
import { Form } from 'formik';
import { ApplicationStore } from '@/state';
import { useStoreState } from 'easy-peasy';
import { SupportIcon } from '@heroicons/react/outline';
import { FaDiscord } from 'react-icons/fa';
import FlashMessageRender from '@/components/FlashMessageRender';
import tw from 'twin.macro';
import { useTranslation } from 'react-i18next';

import Attribution from '@blueprint/extends/Attribution';
import BeforeContent from '@blueprint/components/Authentication/Container/BeforeContent';
import AfterContent from '@blueprint/components/Authentication/Container/AfterContent';

type Props = React.DetailedHTMLProps<React.FormHTMLAttributes<HTMLFormElement>, HTMLFormElement> & {
    title?: string;
};

export default forwardRef<HTMLFormElement, Props>(({ title, ...props }, ref) => {
    const { t } = useTranslation('arix/auth');
    const [guildData, setGuildData] = useState<{ instant_invite: string } | null>(null);

    const name = useStoreState((state: ApplicationStore) => state.settings.data!.name);
    const copyright = useStoreState((state: ApplicationStore) => state.settings.data!.arix.copyright);
    const loginLayout = useStoreState((state: ApplicationStore) => state.settings.data!.arix.loginLayout);
    const logo = useStoreState((state: ApplicationStore) => state.settings.data!.arix.logo);
    const logoHeight = useStoreState((state: ApplicationStore) => state.settings.data!.arix.logoHeight);
    const fullLogo = useStoreState((state: ApplicationStore) => state.settings.data!.arix.fullLogo);
    const socialPosition = useStoreState((state: ApplicationStore) => state.settings.data!.arix.socialPosition);
    const logoPosition = useStoreState((state: ApplicationStore) => state.settings.data!.arix.logoPosition);
    const discord = useStoreState((state: ApplicationStore) => state.settings.data!.arix.discord);
    const support = useStoreState((state: ApplicationStore) => state.settings.data!.arix.support);
    const hasDiscord = !!discord && discord !== 'none';

    useEffect(() => {
        if (!hasDiscord) {
            setGuildData(null);
            return;
        }

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
    }, [discord, hasDiscord]);
    
    return(
        <div className={'my-auto lg:mx-auto'}>
            <FlashMessageRender css={tw`mb-2 px-1`} />
            <BeforeContent />
            <Form {...props} ref={ref}>
                <div className={`max-w-[450px] w-full lg:p-6 p-5 ${loginLayout == 1 ? 'bg-gray-700 rounded-box' : ''}`}>
                    {logoPosition == 1 &&
                    <div className='flex gap-x-2 items-center font-semibold text-lg text-gray-50 pb-5'>
                        <img src={logo} alt={name + 'logo'} css={`height:${logoHeight};`} />
                        {String(fullLogo) === 'false' && name}
                    </div>}
                    {title && <h2 css={tw`text-lg text-gray-50 font-medium mb-3`}>{title}</h2>}
                    {props.children}

                    {socialPosition == 2 &&
                        <div className={'flex justify-center gap-x-6 mt-5'}>
                            {hasDiscord && <>{guildData !== null ? <a className={'flex gap-x-1 items-center duration-300 hover:text-gray-100'} href={guildData.instant_invite}><FaDiscord /> Discord</a> : <a href={''}><FaDiscord />Discord</a>}</>}
                            {support && <a className={'flex gap-x-1 items-center duration-300 hover:text-gray-100'} href={support}><SupportIcon className={'w-5'} />{t('support')}</a>}
                        </div>
                    }
                </div>
            </Form>
            <AfterContent />
            <div className={'mt-4'}>
                <p css={tw`text-center text-neutral-300 text-xs`}>
                    <a
                        rel={'noopener nofollow noreferrer'}
                        href={'https://pterodactyl.io'}
                        target={'_blank'}
                        css={tw`no-underline text-neutral-300 hover:text-neutral-100`}
                    >
                        Pterodactyl&reg;
                    </a>
                    &nbsp;&copy; 2015 - {new Date().getFullYear()}
                    <Attribution />
                </p>
                <p css={tw`text-center text-neutral-300 text-xs`}>
                    {copyright == 'Designed by Weijers.one' ?
                        <>
                        Designed by
                        <span className="px-1 py-[.35rem] font-semibold bg-gray-600 rounded scale-75 inline-block">W.1</span>
                        <a
                            rel={'noopener nofollow noreferrer'}
                            href={'https://arix.gg'}
                            target={'_blank'}
                            css={tw`no-underline text-neutral-300 hover:text-neutral-100`}
                        >
                            Weijers.one
                        </a>
                        </>
                    : copyright}
                </p>
            </div>
        </div>
)});
