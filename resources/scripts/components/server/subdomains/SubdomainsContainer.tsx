import React, { useEffect, useState } from 'react';
import { ServerContext } from '@/state/server';
import { httpErrorToHuman } from '@/api/http';
import FlashMessageRender from '@/components/FlashMessageRender';
import Spinner from '@/components/elements/Spinner';
import Can from '@/components/elements/Can';
import useFlash from '@/plugins/useFlash';
import tw from 'twin.macro';
import TableList from '@/components/elements/TableList';
import ServerContentBlock from '@/components/elements/ServerContentBlock';
import getServerSubdomains, {
    ServerSubdomain,
    SubdomainDomainOption,
} from '@/api/server/subdomains/getServerSubdomains';
import CreateSubdomainButton from '@/components/server/subdomains/CreateSubdomainButton';
import SubdomainRow from '@/components/server/subdomains/SubdomainRow';

const SubdomainsIcon = (props: React.SVGProps<SVGSVGElement>) => (
    <svg fill={'none'} viewBox={'0 0 24 24'} stroke={'currentColor'} {...props}>
        <path
            strokeLinecap={'round'}
            strokeLinejoin={'round'}
            d={
                'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9'
            }
        />
    </svg>
);

export default () => {
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const subdomainLimit = ServerContext.useStoreState((state) => state.server.data!.featureLimits.subdomains);
    const { addError, clearFlashes } = useFlash();
    const [loading, setLoading] = useState(true);
    const [subdomains, setSubdomains] = useState<ServerSubdomain[]>([]);
    const [domains, setDomains] = useState<SubdomainDomainOption[]>([]);

    useEffect(() => {
        clearFlashes('subdomains');
        getServerSubdomains(uuid)
            .then((data) => {
                setSubdomains(data.items);
                setDomains(data.domains);
            })
            .catch((error) => {
                addError({ key: 'subdomains', message: httpErrorToHuman(error) });
            })
            .then(() => setLoading(false));
    }, []);

    const appendSubdomain = (subdomain: ServerSubdomain) => setSubdomains((items) => [...items, subdomain]);
    const removeSubdomain = (id: number) => setSubdomains((items) => items.filter((item) => item.id !== id));

    return (
        <ServerContentBlock title={'Subdomains'} icon={SubdomainsIcon}>
            <FlashMessageRender byKey={'subdomains'} css={tw`mb-4`} />

            {loading ? (
                <Spinner size={'large'} centered />
            ) : (
                <div className={'bg-gray-700 rounded-box backdrop'}>
                    <div className={'flex lg:flex-row flex-col gap-4 items-start justify-between px-6 pt-5 pb-4'}>
                        <div>
                            <p css={tw`text-lg font-semibold text-neutral-50 leading-tight`}>Subdomains</p>
                            <p className={'text-sm text-gray-300 mt-1'}>
                                Manage Cloudflare DNS records for this server.
                            </p>
                            {subdomainLimit > 0 && subdomains.length > 0 && (
                                <p
                                    css={tw`inline-flex mt-3 items-center rounded bg-neutral-800 px-3 py-1 text-xs font-semibold text-neutral-200 border border-neutral-600`}
                                >
                                    {subdomains.length} of {subdomainLimit} subdomains have been allocated.
                                </p>
                            )}
                        </div>
                        <Can action={'subdomain.create'}>
                            {subdomainLimit > 0 && subdomainLimit !== subdomains.length && (
                                <CreateSubdomainButton domains={domains} onCreated={appendSubdomain} />
                            )}
                        </Can>
                    </div>
                    <TableList>
                        <tr>
                            <th css={tw`text-xs font-semibold uppercase tracking-wide`}>FQDN</th>
                            <th css={tw`text-xs font-semibold uppercase tracking-wide`}>Type</th>
                            <th css={tw`text-xs font-semibold uppercase tracking-wide`}>Target</th>
                            <th css={tw`text-xs font-semibold uppercase tracking-wide`}>Minecraft Address</th>
                            <th css={tw`text-xs font-semibold uppercase tracking-wide`}>Proxy</th>
                            <th css={tw`text-xs font-semibold uppercase tracking-wide`}>Status</th>
                            <th></th>
                        </tr>
                        {subdomains.length > 0 ? (
                            subdomains.map((subdomain) => (
                                <SubdomainRow key={subdomain.id} subdomain={subdomain} onDeleted={removeSubdomain} />
                            ))
                        ) : (
                            <tr>
                                <td colSpan={7} css={tw`text-center text-sm`}>
                                    {subdomainLimit > 0
                                        ? 'No subdomains have been created.'
                                        : 'Subdomains cannot be created for this server.'}
                                </td>
                            </tr>
                        )}
                    </TableList>
                </div>
            )}
        </ServerContentBlock>
    );
};
