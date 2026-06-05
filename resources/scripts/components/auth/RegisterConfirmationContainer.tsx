import React, { useEffect, useMemo, useRef, useState } from 'react';
import { Link, RouteComponentProps } from 'react-router-dom';
import { useStoreState } from 'easy-peasy';
import Reaptcha from 'reaptcha';
import tw from 'twin.macro';
import { MailIcon } from '@heroicons/react/outline';
import resendRegistrationEmail from '@/api/auth/resendRegistrationEmail';
import LoginFormContainer from '@/components/auth/LoginFormContainer';
import { Button } from '@/components/elements/button/index';
import { httpErrorToHuman } from '@/api/http';
import useFlash from '@/plugins/useFlash';
import { useTranslation } from 'react-i18next';

interface LocationState {
    email?: string;
}

const maskEmail = (email: string): string => {
    const [localPart, domain = ''] = email.split('@');

    if (!domain) {
        return email;
    }

    const visible = localPart.slice(0, 2);
    return `${visible}${'*'.repeat(Math.max(localPart.length - visible.length, 1))}@${domain}`;
};

const RegisterConfirmationContainer = ({ history, location }: RouteComponentProps) => {
    const { t } = useTranslation('arix/auth');
    const ref = useRef<Reaptcha>(null);
    const [token, setToken] = useState('');
    const [seconds, setSeconds] = useState(8);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const { clearFlashes, addFlash } = useFlash();
    const { enabled: recaptchaEnabled, siteKey } = useStoreState((state) => state.settings.data!.recaptcha);
    const email = ((location.state as LocationState | undefined)?.email || '').toLowerCase();
    const maskedEmail = useMemo(() => maskEmail(email), [email]);

    useEffect(() => {
        clearFlashes();
    }, []);

    useEffect(() => {
        if (!email) {
            history.replace('/auth/register');
            return;
        }

        if (seconds <= 0) {
            history.replace('/auth/login', { flashMessage: t('register.success-message') });
            return;
        }

        const timeout = window.setTimeout(() => setSeconds((value) => value - 1), 1000);
        return () => window.clearTimeout(timeout);
    }, [email, history, seconds, t]);

    const resend = (recaptchaToken?: string) => {
        const currentToken = recaptchaToken || token;

        if (recaptchaEnabled && !currentToken) {
            setIsSubmitting(true);
            ref.current!.execute().catch((error) => {
                console.error(error);
                setIsSubmitting(false);
                addFlash({ type: 'error', title: 'Error', message: httpErrorToHuman(error) });
            });

            return;
        }

        setIsSubmitting(true);

        resendRegistrationEmail(email, currentToken)
            .then((message) => {
                addFlash({ type: 'success', title: 'Success', message });
            })
            .catch((error) => {
                console.error(error);
                addFlash({ type: 'error', title: 'Error', message: httpErrorToHuman(error) });
            })
            .then(() => {
                setIsSubmitting(false);
                setToken('');
                if (ref.current) ref.current.reset();
            });
    };

    return (
        <LoginFormContainer title={'Check your email'} css={tw`w-full flex`}>
            <div css={tw`rounded-box border border-gray-600 bg-gray-700/40 p-5`}>
                <div css={tw`flex items-center gap-3 text-gray-50`}>
                    <MailIcon className={'w-6'} />
                    <h3 css={tw`text-base font-medium`}>Check your email</h3>
                </div>
                <p css={tw`mt-3 text-sm text-neutral-300`}>
                    {t('register.success-message')}
                </p>
                <p css={tw`mt-2 text-sm text-neutral-200`}>{maskedEmail}</p>
                <p css={tw`mt-4 text-xs text-neutral-400`}>
                    Redirecting to login in {seconds}s.
                </p>
            </div>

            <div css={tw`mt-6`}>
                <Button type={'button'} disabled={isSubmitting} className={'w-full !py-3'} onClick={() => resend()}>
                    Resend setup email
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
                            resend(response);
                        }}
                        onExpire={() => {
                            setIsSubmitting(false);
                            setToken('');
                        }}
                    />
                )}
            </div>

            <div css={tw`mt-6 text-center`}>
                <Link to={'/auth/login'} css={tw`text-xs text-neutral-300 tracking-wide uppercase no-underline hover:text-neutral-200`}>
                    {t('reset.return-to-login')}
                </Link>
            </div>
        </LoginFormContainer>
    );
};

export default RegisterConfirmationContainer;
