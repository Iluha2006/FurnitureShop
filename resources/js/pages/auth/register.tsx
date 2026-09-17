import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import TeamInvitationAlert from '@/components/team-invitation-alert';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';
import type { TeamInvitationContext } from '@/types';

type Props = {
    passwordRules: string;
    teamInvitation?: TeamInvitationContext | null;
};

export default function Register({ passwordRules, teamInvitation }: Props) {
    return (
        <>
            <Head title="Регистрация" />
            <Form
                {...store.form()}
                resetOnSuccess={['password', 'password_confirmation']}
                disableWhileProcessing
                className="auth-form"
            >
                {({ processing, errors }) => (
                    <>
                        {teamInvitation && (
                            <TeamInvitationAlert
                                invitation={teamInvitation}
                                action="Register"
                            />
                        )}

                        <div className="auth-fields">
                            <div className="auth-field">
                                <Label htmlFor="name" className="auth-label">
                                    Имя
                                </Label>
                                <Input
                                    id="name"
                                    type="text"
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="name"
                                    name="name"
                                    placeholder="Ваше имя"
                                    className="auth-input"
                                />
                                <InputError
                                    message={errors.name}
                                    className="auth-error"
                                />
                            </div>

                            <div className="auth-field">
                                <Label htmlFor="email" className="auth-label">
                                    Email адрес
                                </Label>
                                <Input
                                    id="email"
                                    type="email"
                                    required
                                    tabIndex={2}
                                    autoComplete="email"
                                    name="email"
                                    placeholder="email@example.com"
                                    className="auth-input"
                                />
                                <InputError message={errors.email} className="auth-error" />
                            </div>

                            <div className="auth-field">
                                <Label htmlFor="password" className="auth-label">
                                    Пароль
                                </Label>
                                <PasswordInput
                                    id="password"
                                    required
                                    tabIndex={3}
                                    autoComplete="new-password"
                                    name="password"
                                    placeholder="Пароль"
                                    passwordrules={passwordRules}
                                    className="auth-input"
                                />
                                <InputError message={errors.password} className="auth-error" />
                            </div>

                            <div className="auth-field">
                                <Label
                                    htmlFor="password_confirmation"
                                    className="auth-label"
                                >
                                    Подтверждение пароля
                                </Label>
                                <PasswordInput
                                    id="password_confirmation"
                                    required
                                    tabIndex={4}
                                    autoComplete="new-password"
                                    name="password_confirmation"
                                    placeholder="Повторите пароль"
                                    passwordrules={passwordRules}
                                    className="auth-input"
                                />
                                <InputError
                                    message={errors.password_confirmation}
                                    className="auth-error"
                                />
                            </div>

                            <Button
                                type="submit"
                                className="auth-btn"
                                tabIndex={5}
                                data-test="register-user-button"
                            >
                                {processing && <Spinner />}
                                Создать аккаунт
                            </Button>
                        </div>

                        <div className="auth-foot">
                            Уже есть аккаунт?{' '}
                            <TextLink
                                href={
                                    teamInvitation
                                        ? login.url({
                                              query: {
                                                  invitation:
                                                      teamInvitation.code,
                                              },
                                          })
                                        : login()
                                }
                                data-test="team-invitation-login-link"
                                tabIndex={6}
                                className="auth-link"
                            >
                                Войти
                            </TextLink>
                        </div>
                    </>
                )}
            </Form>
        </>
    );
}

Register.layout = {
    title: 'Создание аккаунта',
    description: 'Введите данные ниже, чтобы создать аккаунт',
};