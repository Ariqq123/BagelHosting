import React, { useEffect, useRef, useState } from 'react';
import { Link, RouteComponentProps } from 'react-router-dom';
import login from '@/api/auth/login';
import LoginFormContainer from '@/components/auth/LoginFormContainer';
import { useStoreState } from 'easy-peasy';
import { Formik, FormikHelpers } from 'formik';
import { object, string } from 'yup';
import Field from '@/components/elements/Field';
import tw from 'twin.macro';
import { UserCircleIcon, KeyIcon, EyeIcon, EyeOffIcon } from '@heroicons/react/outline';
import { Button } from '@/components/elements/button/index';
import Reaptcha from 'reaptcha';
import useFlash from '@/plugins/useFlash';
import { useTranslation } from 'react-i18next';

interface Values {
    username: string;
    password: string;
}

const LoginContainer = ({ history }: RouteComponentProps) => {
    const { t } = useTranslation('arix/auth');
    const ref = useRef<Reaptcha>(null);
    const [token, setToken] = useState('');
    const [eyeOpen, setEyeOpen] = useState(false);

    const { clearFlashes, clearAndAddHttpError } = useFlash();
    const { enabled: recaptchaEnabled, siteKey } = useStoreState((state) => state.settings.data!.recaptcha);

    useEffect(() => {
        clearFlashes();
    }, []);

    const onSubmit = (values: Values, { setSubmitting }: FormikHelpers<Values>) => {
        clearFlashes();

        // If there is no token in the state yet, request the token and then abort this submit request
        // since it will be re-submitted when the recaptcha data is returned by the component.
        if (recaptchaEnabled && !token) {
            ref.current!.execute().catch((error) => {
                console.error(error);

                setSubmitting(false);
                clearAndAddHttpError({ error });
            });

            return;
        }

        login({ ...values, recaptchaData: token })
            .then((response) => {
                if (response.complete) {
                    // @ts-expect-error this is valid
                    window.location = response.intended || '/';
                    return;
                }

                history.replace('/auth/login/checkpoint', { token: response.confirmationToken });
            })
            .catch((error) => {
                console.error(error);

                setToken('');
                if (ref.current) ref.current.reset();

                setSubmitting(false);
                clearAndAddHttpError({ error });
            });
    };

    return (
        <Formik
            onSubmit={onSubmit}
            initialValues={{ username: '', password: '' }}
            validationSchema={object().shape({
                username: string().required(t('login.must-be-provided')),
                password: string().required(t('login.please-enter-password')),
            })}
        >
            {({ isSubmitting, setSubmitting, submitForm }) => (
                <LoginFormContainer title={t('login.title')} css={tw`w-full flex`}>
                    <Field type={'text'} label={t('login.username-or-email')} placeholder={t('login.username-or-email')} name={'username'} disabled={isSubmitting} icon={UserCircleIcon}/>
                    <div css={tw`mt-6`}>
                        <div className={'flex justify-between'}>
                            <label className={'block text-sm text-gray-300 mb-1 sm:mb-2 font-light'}>{t('login.password')}</label>
                            <Link
                                to={'/auth/password'}
                                css={tw`text-sm text-neutral-300 tracking-wide underline hover:text-neutral-200 font-light`}
                            >
                                {t('login.forgot-password')}
                            </Link>
                        </div>
                        <div className={'relative'}>
                            <Field type={eyeOpen ? 'text' : 'password'} placeholder={t('login.password')} name={'password'} disabled={isSubmitting} icon={KeyIcon} />
                            <button type={'button'} className={'absolute top-2 right-2 p-1 text-gray-300'} onClick={() => setEyeOpen(!eyeOpen)}>
                                {eyeOpen 
                                ? <EyeOffIcon className={'w-5'} />
                                : <EyeIcon className={'w-5'} />}
                            </button>
                        </div>
                    </div>
                    <div css={tw`mt-6`}>
                        <Button type={'submit'} className={'w-full !py-3'} disabled={isSubmitting}>
                            {t('login.login')}
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
                </LoginFormContainer>
            )}
        </Formik>
    );
};

export default LoginContainer;
