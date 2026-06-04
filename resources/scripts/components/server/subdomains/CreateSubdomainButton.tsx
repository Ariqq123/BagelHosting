import React, { useMemo, useState } from 'react';
import Modal from '@/components/elements/Modal';
import { Field as FormikField, Form, Formik, FormikHelpers } from 'formik';
import Field from '@/components/elements/Field';
import Label from '@/components/elements/Label';
import Select from '@/components/elements/Select';
import { object, string, number } from 'yup';
import createServerSubdomain from '@/api/server/subdomains/createServerSubdomain';
import { ServerContext } from '@/state/server';
import { httpErrorToHuman } from '@/api/http';
import FlashMessageRender from '@/components/FlashMessageRender';
import useFlash from '@/plugins/useFlash';
import { Button } from '@/components/elements/button/index';
import { ServerSubdomain, SubdomainDomainOption } from '@/api/server/subdomains/getServerSubdomains';
import tw from 'twin.macro';

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
                            <h2 css={tw`font-header text-xl font-medium mb-2 text-gray-50`}>Create subdomain</h2>
                            <Form css={tw`m-0`}>
                                <Field
                                    type={'string'}
                                    id={'subdomain_name'}
                                    name={'name'}
                                    label={'Prefix'}
                                    description={'Only lowercase letters, numbers, and hyphens are allowed.'}
                                />
                                <div css={tw`mt-6`}>
                                    <Label>Domain</Label>
                                    <FormikField
                                        as={Select}
                                        name={'domainId'}
                                        onChange={(event: React.ChangeEvent<HTMLSelectElement>) => {
                                            const next = domains.find(
                                                (domain) => domain.id === Number(event.target.value)
                                            );
                                            setFieldValue('domainId', Number(event.target.value));
                                            setFieldValue('type', next?.allowedRecordTypes[0] || 'A');
                                        }}
                                    >
                                        {domains.map((domain) => (
                                            <option key={domain.id} value={domain.id}>
                                                {domain.name}
                                            </option>
                                        ))}
                                    </FormikField>
                                </div>
                                <div css={tw`mt-6`}>
                                    <Label>Record type</Label>
                                    <FormikField as={Select} name={'type'}>
                                        {types.map((type) => (
                                            <option key={type} value={type}>
                                                {type}
                                            </option>
                                        ))}
                                    </FormikField>
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
                                        Create
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
