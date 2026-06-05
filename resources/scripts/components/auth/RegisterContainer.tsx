import React, { useEffect, useRef, useState } from 'react';
import { Link, RouteComponentProps } from 'react-router-dom';
import { useStoreState } from 'easy-peasy';
import { Formik, FormikHelpers } from 'formik';
import { object, string } from 'yup';
import { AtSymbolIcon, UserCircleIcon } from '@heroicons/react/outline';
import Reaptcha from 'reaptcha';
import tw from 'twin.macro';
import register from '@/api/auth/register';
import Field from '@/components/elements/Field';
import LoginFormContainer from '@/components/auth/LoginFormContainer';
import { Button } from '@/components/elements/button/index';
import { httpErrorToHuman } from '@/api/http';
import useFlash from '@/plugins/useFlash';
import { useTranslation } from 'react-i18next';

interface Values {
    email: string;
    username: string;
    firstName: string;
    lastName: string;
}

interface LocationState {
    email?: string;
}

const RegisterContainer = ({ history }: RouteComponentProps) => {
    const { t } = useTranslation('arix/auth');
    const ref = useRef<Reaptcha>(null);
    const [token, setToken] = useState('');
    const { clearFlashes, addFlash } = useFlash();
    const { enabled: recaptchaEnabled, siteKey } = useStoreState((state) => state.settings.data!.recaptcha);

    useEffect(() => {
        clearFlashes();
    }, []);

    const onSubmit = ({ email, username, firstName, lastName }: Values, { setSubmitting }: FormikHelpers<Values>) => {
        clearFlashes();

        if (recaptchaEnabled && !token) {
            ref.current!.execute().catch((error) => {
                console.error(error);
                setSubmitting(false);
                addFlash({ type: 'error', title: 'Error', message: httpErrorToHuman(error) });
            });

            return;
        }

        register({ email, username, firstName, lastName, recaptchaData: token })
            .then((response) => {
                history.replace(response.redirectTo || '/auth/register/confirmation', { email: response.email } as LocationState);
            })
            .catch((error) => {
                console.error(error);
                setSubmitting(false);
                setToken('');
                if (ref.current) ref.current.reset();
                addFlash({ type: 'error', title: 'Error', message: httpErrorToHuman(error) });
            });
    };

    return (
        <Formik
            onSubmit={onSubmit}
            initialValues={{ email: '', username: '', firstName: '', lastName: '' }}
            validationSchema={object().shape({
                email: string().email(t('register.valid-email-required')).required(t('register.email-required')),
                username: string().required(t('register.username-required')),
                firstName: string().required(t('register.firstname-required')),
                lastName: string().required(t('register.lastname-required')),
            })}
        >
            {({ isSubmitting, setSubmitting, submitForm }) => (
                <LoginFormContainer title={t('register.title')} css={tw`w-full flex`}>
                    <Field label={t('register.email')} placeholder={t('register.email')} name={'email'} type={'email'} icon={AtSymbolIcon} />
                    <div css={tw`mt-6`}>
                        <Field label={t('register.username')} placeholder={t('register.username')} name={'username'} type={'text'} icon={UserCircleIcon} />
                    </div>
                    <div css={tw`mt-6`}>
                        <Field label={t('register.firstname')} placeholder={t('register.firstname')} name={'firstName'} type={'text'} icon={UserCircleIcon} />
                    </div>
                    <div css={tw`mt-6`}>
                        <Field label={t('register.lastname')} placeholder={t('register.lastname')} name={'lastName'} type={'text'} icon={UserCircleIcon} />
                    </div>
                    <div css={tw`mt-6`}>
                        <Button type={'submit'} disabled={isSubmitting} className={'w-full !py-3'}>
                            {t('register.register')}
                        </Button>
                    </div>
                    <div className={'z-50 relative'}>
                        {recaptchaEnabled && (
                            <Reaptcha
                                ref={ref}
                                size={'invisible'}
                                sitekey={siteKey || '_invalid_key'}
                                onVerify={(response) => {
                                    setToken(response);
                                    submitForm();
                                }}
                                onExpire={() => {
                                    setSubmitting(false);
                                    setToken('');
                                }}
                            />
                        )}
                    </div>
                    <div css={tw`mt-6 text-center`}>
                        <span css={tw`text-sm text-neutral-300 mr-1`}>{t('register.already-have-account')}</span>
                        <Link to={'/auth/login'} css={tw`text-sm text-neutral-300 underline hover:text-neutral-200`}>
                            {t('login.login')}
                        </Link>
                    </div>
                </LoginFormContainer>
            )}
        </Formik>
    );
};

export default RegisterContainer;
