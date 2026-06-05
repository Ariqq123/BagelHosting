import React from 'react';
import { render, screen } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import RegisterConfirmationContainer from '@/components/auth/RegisterConfirmationContainer';

jest.mock('easy-peasy', () => ({
    useStoreState: (callback: (state: any) => any) => callback({
        settings: {
            data: {
                recaptcha: {
                    enabled: false,
                    siteKey: '',
                },
            },
        },
    }),
}));

jest.mock('react-i18next', () => ({
    useTranslation: () => ({
        t: (key: string) => ({
            'register.title': 'Create an Account',
            'register.success-message': 'Check your email to finish setting your password.',
            'reset.return-to-login': 'Return to login',
        }[key] || key),
    }),
}));

jest.mock('@/plugins/useFlash', () => () => ({
    clearFlashes: jest.fn(),
    addFlash: jest.fn(),
}));

jest.mock('@/components/auth/LoginFormContainer', () => ({
    __esModule: true,
    default: ({ title, children }: { title?: string; children: React.ReactNode }) => (
        <div>
            <h1>{title}</h1>
            {children}
        </div>
    ),
}));

jest.mock('@/api/auth/resendRegistrationEmail', () => ({
    __esModule: true,
    default: jest.fn(),
}));

jest.mock('reaptcha', () => () => null);

describe('RegisterConfirmationContainer', () => {
    it('renders confirmation content for submitted registration', async () => {
        render(
            <MemoryRouter>
                <RegisterConfirmationContainer
                    history={{ replace: jest.fn() } as any}
                    location={{ state: { email: 'new.user@example.com' } } as any}
                    match={{ params: {} } as any}
                />
            </MemoryRouter>
        );

        expect(await screen.findByText('Check your email')).toBeInTheDocument();
        expect(screen.getByText('ne******@example.com')).toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Resend setup email' })).toBeInTheDocument();
    });
});
