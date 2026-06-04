import React from 'react';
import { Server } from '@/api/server/getServer';
import getServers from '@/api/getServers';
import useSWR from 'swr';
import { PaginatedResult } from '@/api/http';
import { useHistory } from 'react-router-dom';
import Select from '@/components/elements/Select';
import { useTranslation } from 'react-i18next';

export default () => {
    const { t } = useTranslation('arix/navigation');

    const { data: servers } = useSWR<PaginatedResult<Server>>(['/api/client/servers', 'selector'], () =>
        getServers({ page: 1, per_page: 100 })
    );

    const history = useHistory();

    const handleChange = (value: string) => {
        history.push(value);
    };

    return (
        <div className={'w-[250px]'}>
            <Select
                onChange={(event) => handleChange(event.target.value)}
                value={window.location.pathname.split('/', 3).join('/')}
                className={'selection-container'}
            >
                <option value={`/`} hidden>
                    {t('select-a-server')}
                </option>
                {!servers ? (
                    <option value={`/`} disabled>
                        {t('loading')}
                    </option>
                ) : servers.items.length > 0 ? (
                    servers.items.map((server) => (
                        <option value={`/server/${server.id}`} key={server.uuid}>
                            {server.name}
                        </option>
                    ))
                ) : (
                    <option value={`/`} disabled>
                        {t('no-servers')}
                    </option>
                )}
            </Select>
        </div>
    );
};
