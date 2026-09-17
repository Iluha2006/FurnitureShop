import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import PasskeyVerify from '@/components/passkey-verify';
import TeamInvitationAlert from '@/components/team-invitation-alert';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import type { TeamInvitationContext } from '@/types';

type Props = {
    status?: string;
    canResetPassword: boolean;
    teamInvitation?: TeamInvitationContext | null;
};

export default function Login({
    status,
    canResetPassword,
    teamInvitation,
}: Props) {
    return (
        <>
            <Head title="Вход" />

            {teamInvitation && (
                <TeamInvitationAlert
                    invitation={teamInvitation}
                    action="Log in"
                />
            )}

            <PasskeyVerify />

            <Form
                {...store.form()}
                resetOnSuccess={['password']}
                className="auth-form"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="auth-fields">
                            <div className="auth-field">
                                <Label htmlFor="email" className="auth-label">
                                    Email адрес
                                </Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="email"
                                    placeholder="email@example.com"
                                    className="auth-input"
                                />
                                <InputError message={errors.email} className="auth-error" />
                            </div>

                            <div className="auth-field">
                                <div className="auth-row">
                                    <Label htmlFor="password" className="auth-label">
                                        Пароль
                                    </Label>
                                    {canResetPassword && (
                                        <TextLink
                                            href={request()}
                                            className="auth-link"
                                            tabIndex={5}
                                        >
                                            Забыли пароль?
                                        </TextLink>
                                    )}
                                </div>
                                <PasswordInput
                                    id="password"
                                    name="password"
                                    required
                                    tabIndex={2}
                                    autoComplete="current-password"
                                    placeholder="Пароль"
                                    className="auth-input"
                                />
                                <InputError message={errors.password} className="auth-error" />
                            </div>

                            <div className="auth-row auth-row--check">
                                <Checkbox
                                    id="remember"
                                    name="remember"
                                    tabIndex={3}
                                    className="auth-checkbox"
                                />
                                <Label htmlFor="remember" className="auth-label">
                                    Запомнить меня
                                </Label>
                            </div>

                            <Button
                                type="submit"
                                className="auth-btn"
                                tabIndex={4}
                                disabled={processing}
                                data-test="login-button"
                            >
                                {processing && <Spinner />}
                                Войти
                            </Button>
                        </div>

                        <div className="auth-foot">
                            Нет аккаунта?{' '}
                            <TextLink
                                href={register({
                                    query: {
                                        invitation: teamInvitation?.code,
                                    },
                                })}
                                data-test="register-link"
                                tabIndex={5}
                                className="auth-link"
                            >
                                Зарегистрироваться
                            </TextLink>
                        </div>
                    </>
                )}
            </Form>

            {status && (
                <div className="auth-status">{status}</div>
            )}
        </>
    );
}

Login.layout = {
    title: 'Вход в аккаунт',
    description: 'Введите email и пароль, чтобы войти',
};