import React, { useState } from 'react';
import Modal from '@/components/elements/Modal';
import Can from '@/components/elements/Can';
import CopyOnClick from '@/components/elements/CopyOnClick';
import { Button } from '@/components/elements/button/index';
import { TrashIcon } from '@heroicons/react/outline';
import { ServerContext } from '@/state/server';
import { httpErrorToHuman } from '@/api/http';
import deleteServerSubdomain from '@/api/server/subdomains/deleteServerSubdomain';
import { ServerSubdomain } from '@/api/server/subdomains/getServerSubdomains';
import useFlash from '@/plugins/useFlash';
import tw from 'twin.macro';

interface Props {
    subdomain: ServerSubdomain;
    onDeleted: (id: number) => void;
}

const badgeStyles = tw`inline-flex items-center rounded px-2 py-1 text-xs font-semibold uppercase tracking-wide`;
const cellStyles = tw`align-middle text-sm text-neutral-200`;
const monoTextStyles = tw`font-mono text-sm font-semibold text-neutral-50 leading-5`;
const mutedTextStyles = tw`text-xs text-neutral-300 leading-5`;

export default ({ subdomain, onDeleted }: Props) => {
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { addError, clearFlashes } = useFlash();
    const [visible, setVisible] = useState(false);
    const [loading, setLoading] = useState(false);

    const submit = () => {
        setLoading(true);
        clearFlashes('subdomains');
        deleteServerSubdomain(uuid, subdomain.id)
            .then(() => {
                setVisible(false);
                onDeleted(subdomain.id);
            })
            .catch((error) => {
                addError({ key: 'subdomains', message: httpErrorToHuman(error) });
            })
            .then(() => setLoading(false));
    };

    return (
        <>
            <Modal
                visible={visible}
                dismissable={!loading}
                showSpinnerOverlay={loading}
                onDismissed={() => setVisible(false)}
            >
                <h2 css={tw`text-2xl mb-6`}>Delete subdomain</h2>
                <p css={tw`text-sm`}>
                    Delete <strong>{subdomain.fqdn}</strong> from Cloudflare and this server?
                </p>
                <div css={tw`mt-6 text-right`}>
                    <Button
                        type={'button'}
                        variant={Button.Variants.Secondary}
                        css={tw`mr-2`}
                        onClick={() => setVisible(false)}
                    >
                        Cancel
                    </Button>
                    <Button.Danger type={'button'} onClick={submit}>
                        Delete
                    </Button.Danger>
                </div>
            </Modal>
            <tr css={tw`hover:bg-neutral-700 hover:bg-opacity-40 transition-colors`}>
                <td css={cellStyles}>
                    <CopyOnClick text={subdomain.fqdn}>
                        <p css={monoTextStyles}>{subdomain.fqdn}</p>
                    </CopyOnClick>
                    {subdomain.errorMessage && (
                        <p css={tw`text-xs font-medium text-red-300 mt-1`}>{subdomain.errorMessage}</p>
                    )}
                </td>
                <td css={cellStyles}>
                    <span
                        css={[
                            badgeStyles,
                            tw`bg-primary-500 bg-opacity-20 text-primary-100 border border-primary-400 border-opacity-40`,
                        ]}
                    >
                        {subdomain.type}
                    </span>
                </td>
                <td css={cellStyles}>
                    <CopyOnClick text={subdomain.content}>
                        <p css={monoTextStyles}>{subdomain.content}</p>
                    </CopyOnClick>
                </td>
                <td css={cellStyles}>
                    {subdomain.minecraftAddress ? (
                        <CopyOnClick text={subdomain.minecraftAddress}>
                            <p css={monoTextStyles}>{subdomain.minecraftAddress}</p>
                        </CopyOnClick>
                    ) : (
                        <span css={tw`text-sm font-medium text-neutral-400`}>None</span>
                    )}
                    {subdomain.srvPort && <p css={mutedTextStyles}>SRV port {subdomain.srvPort}</p>}
                </td>
                <td css={cellStyles}>
                    <span
                        css={[
                            badgeStyles,
                            subdomain.proxied
                                ? tw`bg-yellow-500 bg-opacity-20 text-yellow-100 border border-yellow-400 border-opacity-40`
                                : tw`bg-neutral-600 text-neutral-100 border border-neutral-500`,
                        ]}
                    >
                        {subdomain.proxied ? 'Proxied' : 'DNS only'}
                    </span>
                </td>
                <td css={cellStyles}>
                    <span
                        css={[
                            badgeStyles,
                            subdomain.status === 'active'
                                ? tw`bg-green-500 bg-opacity-20 text-green-100 border border-green-400 border-opacity-40`
                                : tw`bg-red-500 bg-opacity-20 text-red-100 border border-red-400 border-opacity-40`,
                        ]}
                    >
                        {subdomain.status}
                    </span>
                </td>
                <td className={'w-1 align-middle'}>
                    <div className={'flex justify-end'}>
                        <Can action={'subdomain.delete'}>
                            <Button.Danger onClick={() => setVisible(true)}>
                                <TrashIcon className={'w-5'} />
                            </Button.Danger>
                        </Can>
                    </div>
                </td>
            </tr>
        </>
    );
};
