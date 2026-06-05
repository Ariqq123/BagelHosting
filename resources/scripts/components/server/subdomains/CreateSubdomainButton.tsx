import React, { useMemo, useState } from 'react';
import Modal from '@/components/elements/Modal';
import { Form, Formik, FormikHelpers } from 'formik';
import Field from '@/components/elements/Field';
import { object, string, number } from 'yup';
import createServerSubdomain from '@/api/server/subdomains/createServerSubdomain';
import { ServerContext } from '@/state/server';
import { httpErrorToHuman } from '@/api/http';
import FlashMessageRender from '@/components/FlashMessageRender';
import useFlash from '@/plugins/useFlash';
import { Button } from '@/components/elements/button/index';
import { ServerSubdomain, SubdomainDomainOption } from '@/api/server/subdomains/getServerSubdomains';
import tw from 'twin.macro';
import styled, { css } from 'styled-components/macro';

interface Props {
    domains: SubdomainDomainOption[];
    onCreated: (subdomain: ServerSubdomain) => void;
}

interface Values {
    name: string;
    domainId: number;
    type: string;
}

const schema = object().shape({
    name: string()
        .required('A subdomain prefix must be provided.')
        .matches(/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/, 'Use lowercase letters, numbers, and hyphens only.'),
    domainId: number().required('A domain must be selected.'),
    type: string().required('A record type must be selected.').oneOf(['A', 'CNAME']),
});

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

const DomainButton = styled.button<{ $selected: boolean }>`
    ${tw`w-full text-left rounded border p-3 transition-colors duration-150`};
    ${(props) =>
        props.$selected
            ? tw`border-primary-300 bg-neutral-500 text-neutral-50`
            : tw`border-neutral-500 bg-neutral-700 text-neutral-200 hover:border-neutral-400 hover:bg-neutral-600`};

    &:focus {
        ${tw`outline-none border-primary-300 ring-1 ring-primary-300`};
    }
`;

const TypeButton = styled.button<{ $selected: boolean }>`
    ${tw`flex-1 rounded px-3 py-2 text-sm font-medium transition-colors duration-150`};
    ${(props) =>
        props.$selected
            ? tw`bg-primary-500 text-primary-50`
            : tw`bg-neutral-700 text-neutral-200 hover:bg-neutral-600`};

    &:focus {
        ${tw`outline-none ring-1 ring-primary-300`};
    }

    &:disabled {
        ${tw`cursor-not-allowed opacity-50`};
    }
`;

const Pill = styled.span<{ $selected?: boolean }>`
    ${tw`inline-flex items-center rounded px-2 py-1 text-xs font-medium`};
    ${(props) =>
        props.$selected
            ? css`
                  ${tw`bg-primary-400 text-primary-50`};
              `
            : css`
                  ${tw`bg-neutral-800 text-neutral-300`};
              `}
`;

export default ({ domains, onCreated }: Props) => {
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { addError, clearFlashes } = useFlash();
    const [visible, setVisible] = useState(false);

    const initialValues = useMemo<Values>(() => {
        const domain = domains[0];

        return {
            name: '',
            domainId: domain?.id || 0,
            type: domain?.allowedRecordTypes[0] || 'A',
        };
    }, [domains]);

    const submit = (values: Values, { setSubmitting }: FormikHelpers<Values>) => {
        clearFlashes('subdomains:create');
        createServerSubdomain(uuid, values)
            .then((subdomain) => {
                onCreated(subdomain);
                setVisible(false);
            })
            .catch((error) => {
                addError({ key: 'subdomains:create', message: httpErrorToHuman(error) });
                setSubmitting(false);
            });
    };

    return (
        <>
            <Formik enableReinitialize onSubmit={submit} initialValues={initialValues} validationSchema={schema}>
                {({ isSubmitting, values, setFieldValue, resetForm }) => {
                    const selected = domains.find((domain) => domain.id === Number(values.domainId));
                    const types = selected?.allowedRecordTypes || [];
                    const fqdn = [values.name || 'prefix', selected?.name].filter(Boolean).join('.');

                    return (
                        <Modal
                            visible={visible}
                            dismissable={!isSubmitting}
                            showSpinnerOverlay={isSubmitting}
                            onDismissed={() => {
                                resetForm();
                                setVisible(false);
                            }}
                        >
                            <FlashMessageRender byKey={'subdomains:create'} css={tw`mb-6`} />
                            <div css={tw`flex items-start mb-5`}>
                                <div
                                    css={tw`flex items-center justify-center w-12 h-12 rounded bg-neutral-700 mr-4 text-primary-300`}
                                >
                                    <SubdomainsIcon css={tw`w-7 h-7`} />
                                </div>
                                <div css={tw`min-w-0`}>
                                    <h2 css={tw`font-header text-xl font-medium text-gray-50`}>Create subdomain</h2>
                                    <p css={tw`text-sm text-neutral-300 mt-1`}>
                                        Point a hostname at this server through Cloudflare DNS. A records also create a
                                        Minecraft SRV record so players can connect without typing the port.
                                    </p>
                                </div>
                            </div>
                            <Form css={tw`m-0`}>
                                <div css={tw`rounded bg-neutral-700 border border-neutral-500 p-4 mb-5`}>
                                    <p css={tw`text-xs uppercase tracking-wide text-neutral-300 mb-1`}>Preview</p>
                                    <p css={tw`font-mono text-sm text-neutral-50 break-all`}>{fqdn}</p>
                                </div>
                                <Field
                                    type={'string'}
                                    id={'subdomain_name'}
                                    name={'name'}
                                    label={'Prefix'}
                                    description={'Only lowercase letters, numbers, and hyphens are allowed.'}
                                />
                                <div css={tw`mt-6`}>
                                    <p css={tw`text-sm text-neutral-200 mb-2`}>Domain</p>
                                    <div css={tw`space-y-2 max-h-48 overflow-y-auto pr-1`}>
                                        {domains.length > 0 ? (
                                            domains.map((domain) => (
                                                <DomainButton
                                                    key={domain.id}
                                                    type={'button'}
                                                    $selected={domain.id === Number(values.domainId)}
                                                    aria-pressed={domain.id === Number(values.domainId)}
                                                    onClick={() => {
                                                        setFieldValue('domainId', domain.id);
                                                        setFieldValue('type', domain.allowedRecordTypes[0] || 'A');
                                                    }}
                                                >
                                                    <span css={tw`flex items-center justify-between gap-3`}>
                                                        <span css={tw`font-medium break-all`}>{domain.name}</span>
                                                        <span css={tw`flex flex-shrink-0 gap-1`}>
                                                            {domain.allowedRecordTypes.map((type) => (
                                                                <Pill
                                                                    key={type}
                                                                    $selected={domain.id === Number(values.domainId)}
                                                                >
                                                                    {type}
                                                                </Pill>
                                                            ))}
                                                        </span>
                                                    </span>
                                                </DomainButton>
                                            ))
                                        ) : (
                                            <div
                                                css={tw`rounded border border-neutral-500 bg-neutral-700 p-4 text-sm text-neutral-300`}
                                            >
                                                No subdomain domains are available.
                                            </div>
                                        )}
                                    </div>
                                </div>
                                <div css={tw`mt-6`}>
                                    <p css={tw`text-sm text-neutral-200 mb-2`}>Record type</p>
                                    <div css={tw`flex gap-2 rounded bg-neutral-800 p-1`}>
                                        {['A', 'CNAME'].map((type) => (
                                            <TypeButton
                                                key={type}
                                                type={'button'}
                                                $selected={values.type === type}
                                                aria-pressed={values.type === type}
                                                disabled={!types.includes(type)}
                                                onClick={() => setFieldValue('type', type)}
                                            >
                                                {type}
                                            </TypeButton>
                                        ))}
                                    </div>
                                </div>
                                <div css={tw`flex flex-wrap justify-end mt-6`}>
                                    <Button
                                        variant={Button.Variants.Secondary}
                                        type={'button'}
                                        css={tw`w-full sm:w-auto sm:mr-2`}
                                        onClick={() => setVisible(false)}
                                    >
                                        Cancel
                                    </Button>
                                    <Button
                                        css={tw`w-full mt-4 sm:w-auto sm:mt-0`}
                                        type={'submit'}
                                        disabled={!domains.length}
                                    >
                                        Create Subdomain
                                    </Button>
                                </div>
                            </Form>
                        </Modal>
                    );
                }}
            </Formik>
            <Button disabled={!domains.length} onClick={() => setVisible(true)}>
                New Subdomain
            </Button>
        </>
    );
};
