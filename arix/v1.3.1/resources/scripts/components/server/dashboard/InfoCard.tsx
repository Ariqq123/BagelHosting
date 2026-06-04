import React from 'react';
import TitledGreyBox from '@/components/elements/TitledGreyBox';
import { ServerContext } from '@/state/server';
import CopyOnClick from '@/components/elements/CopyOnClick';
import { ip } from '@/lib/formatters';
import { HashtagIcon, ServerIcon } from '@heroicons/react/outline';
import { useTranslation } from 'react-i18next';

const InfoCard = () => {
    const { t } = useTranslation('arix/server/dashboard');
    const node = ServerContext.useStoreState((state) => state.server.data!.node);
    const id = ServerContext.useStoreState((state) => state.server.data!.id);

    const hostname = ServerContext.useStoreState((state) => {
        const match = state.server.data?.allocations.find((allocation) => allocation.isDefault);

        return !match ? 'n/a' : `${match.alias || ip(match.ip)}:${match.port}`;
    });

    return(
        <TitledGreyBox title={t('server-info.title')}>
            <div className={'grid grid-cols-2 py-4 border-b-2 border-gray-600'}>
                <span className={'text-gray-300'}>{t('server-info.hostname')}:</span>
                <CopyOnClick text={hostname}>
                    <p>{hostname}</p>
                </CopyOnClick>
            </div>
            <div className={'grid grid-cols-2 py-4 border-b-2 border-gray-600'}>
                <span className={'text-gray-300'}>{t('server-info.node-id')}:</span>
                <CopyOnClick text={node}>
                    <div className={'flex items-center gap-x-1'}>
                        <ServerIcon className={'w-5 text-gray-300'}/>
                        {node}
                    </div>
                </CopyOnClick>
            </div>
            <div className={'grid grid-cols-2 pt-4'}>
                <span className={'text-gray-300'}>{t('server-info.server-id')}:</span>
                <CopyOnClick text={id}>
                    <div className={'flex items-center gap-x-1'}>
                        <HashtagIcon className={'w-5 text-gray-300'}/>
                        {id}
                    </div>
                </CopyOnClick>
            </div>
        </TitledGreyBox>
    )
}

export default InfoCard;