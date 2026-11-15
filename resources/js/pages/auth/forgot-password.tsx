// Components
import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { login } from '@/routes';
import { email } from '@/routes/password';

const RESET_LINK_SENT =
    'Ссылка для восстановления пароля отправлена на указанный email.';

export default function ForgotPassword({ status }: { status?: string }) {
    return (
        <>
            <Head title="Восстановление пароля" />

            <Form {...email.form()} className="auth-form">
                {({ processing, errors }) => (
                    <>
                        <div className="auth-fields">
                            <div className="auth-field">
                                <Label
                                    htmlFor="email"
                                    className="auth-label"
                                >
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
                                <InputError
                                    message={errors.email}
                                    className="auth-error"
                                />
                            </div>

                            <Button
                                type="submit"
                                className="auth-btn"
                                tabIndex={2}
                                disabled={processing}
                                data-test="email-password-reset-link-button"
                            >
                                {processing && <Spinner />}
                                Отправить ссылку для восстановления
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

            {status && (
                <div className="auth-status">
                    {RESET_LINK_SENT}
                </div>
            )}
        </>
    );
}

ForgotPassword.layout = {
    title: 'Восстановление пароля',
    description:
        'Укажите email, на который мы отправим ссылку для сброса пароля',
};
