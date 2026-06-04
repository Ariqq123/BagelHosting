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
            <tr>
                <td>
                    <CopyOnClick text={subdomain.fqdn}>
                        <p>{subdomain.fqdn}</p>
                    </CopyOnClick>
                    {subdomain.errorMessage && <p css={tw`text-xs text-red-300 mt-1`}>{subdomain.errorMessage}</p>}
                </td>
                <td>{subdomain.type}</td>
                <td>
                    <CopyOnClick text={subdomain.content}>
                        <p>{subdomain.content}</p>
                    </CopyOnClick>
                </td>
                <td>{subdomain.proxied ? 'Proxied' : 'DNS only'}</td>
                <td>{subdomain.status}</td>
                <td className={'w-1'}>
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
