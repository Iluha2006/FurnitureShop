// Components
import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/password/confirm';
import {
    index as confirmOptions,
    store as confirmStore,
} from '@/actions/Laravel/Passkeys/Http/Controllers/PasskeyConfirmationController';
import PasskeyVerify from '@/components/passkey-verify';

export default function ConfirmPassword() {
    return (
        <>
            <Head title="Подтверждение пароля" />

            <PasskeyVerify
                routes={{
                    options: confirmOptions(),
                    submit: confirmStore(),
                }}
                label="Подтвердить с помощью пассключа"
                loadingLabel="Подтверждение..."
                separator="Или подтвердите паролем"
            />

            <Form
                {...store.form()}
                resetOnSuccess={['password']}
                className="auth-form"
            >
                {({ processing, errors }) => (
                    <div className="auth-fields">
                        <div className="auth-field">
                            <Label htmlFor="password" className="auth-label">
                                Пароль
                            </Label>
                            <PasswordInput
                                id="password"
                                name="password"
                                placeholder="Пароль"
                                autoComplete="current-password"
                                autoFocus
                                className="auth-input"
                            />

                            <InputError
                                message={errors.password}
                                className="auth-error"
                            />
                        </div>

                        <Button
                            type="submit"
                            className="auth-btn"
                            disabled={processing}
                            data-test="confirm-password-button"
                        >
                            {processing && <Spinner />}
                            Подтвердить пароль
                        </Button>
                    </div>
                )}
            </Form>
        </>
    );
}

ConfirmPassword.layout = {
    title: 'Подтверждение пароля',
    description:
        'Это защищённая зона приложения. Пожалуйста, подтвердите пароль, чтобы продолжить.',
};
