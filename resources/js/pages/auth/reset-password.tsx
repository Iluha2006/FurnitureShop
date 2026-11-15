// Components
import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { update } from '@/routes/password';

type Props = {
    token: string;
    email: string;
    passwordRules: string;
};

export default function ResetPassword({ token, email, passwordRules }: Props) {
    return (
        <>
            <Head title="Новый пароль" />

            <Form
                {...update.form()}
                transform={(data) => ({ ...data, token, email })}
                resetOnSuccess={['password', 'password_confirmation']}
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
                                    autoComplete="email"
                                    value={email}
                                    readOnly
                                    className="auth-input"
                                />
                                <InputError
                                    message={errors.email}
                                    className="auth-error"
                                />
                            </div>

                            <div className="auth-field">
                                <Label
                                    htmlFor="password"
                                    className="auth-label"
                                >
                                    Новый пароль
                                </Label>
                                <PasswordInput
                                    id="password"
                                    name="password"
                                    autoComplete="new-password"
                                    autoFocus
                                    placeholder="Новый пароль"
                                    className="auth-input"
                                    passwordrules={passwordRules}
                                />
                                <InputError
                                    message={errors.password}
                                    className="auth-error"
                                />
                            </div>

                            <div className="auth-field">
                                <Label
                                    htmlFor="password_confirmation"
                                    className="auth-label"
                                >
                                    Повторите пароль
                                </Label>
                                <PasswordInput
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    autoComplete="new-password"
                                    placeholder="Повторите пароль"
                                    className="auth-input"
                                    passwordrules={passwordRules}
                                />
                                <InputError
                                    message={errors.password_confirmation}
                                    className="auth-error"
                                />
                            </div>

                            <Button
                                type="submit"
                                className="auth-btn"
                                disabled={processing}
                                data-test="reset-password-button"
                            >
                                {processing && <Spinner />}
                                Сохранить пароль
                            </Button>
                        </div>

                        <div className="auth-foot">
                            Вспомнили пароль?{' '}
                            <TextLink
                                href={login()}
                                className="auth-link"
                                tabIndex={3}
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

ResetPassword.layout = {
    title: 'Новый пароль',
    description: 'Введите новый пароль для вашего аккаунта',
};
