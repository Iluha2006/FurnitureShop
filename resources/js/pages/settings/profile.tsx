import { Form, Head, Link, usePage } from '@inertiajs/react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/delete-user';
import InputError from '@/components/input-error';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { send } from '@/routes/verification';
import type { Auth } from '@/types';

type PageProps = {
    auth: Auth;
};

export default function Profile({
    mustVerifyEmail,
    status,
}: {
    mustVerifyEmail: boolean;
    status?: string;
}) {
    const { auth } = usePage<PageProps>().props;

    return (
        <>
            <Head title="Profile settings" />

            <h1 className="sr-only">Profile settings</h1>

            <div className="profile">
                <section className="profile__panel">
                    <h2 className="profile__title">Profile</h2>
                    <p className="profile__desc">
                        Update your name and email address
                    </p>

                    <Form
                        {...ProfileController.update.form()}
                        options={{
                            preserveScroll: true,
                        }}
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="profile__fields">
                                    <div className="profile__field">
                                        <Label
                                            htmlFor="name"
                                            className="profile__label"
                                        >
                                            Name
                                        </Label>

                                        <Input
                                            id="name"
                                            className="profile__input"
                                            defaultValue={auth.user.name}
                                            name="name"
                                            required
                                            autoComplete="name"
                                            placeholder="Full name"
                                        />

                                        <InputError
                                            className="profile__error"
                                            message={errors.name}
                                        />
                                    </div>

                                    <div className="profile__field">
                                        <Label
                                            htmlFor="email"
                                            className="profile__label"
                                        >
                                            Email address
                                        </Label>

                                        <Input
                                            id="email"
                                            type="email"
                                            className="profile__input"
                                            defaultValue={auth.user.email}
                                            name="email"
                                            required
                                            autoComplete="username"
                                            placeholder="Email address"
                                        />

                                        <InputError
                                            className="profile__error"
                                            message={errors.email}
                                        />
                                    </div>

                                    {mustVerifyEmail &&
                                        auth.user.email_verified_at ===
                                            null && (
                                            <div className="profile__field">
                                                <p className="profile__hint">
                                                    Your email address is
                                                    unverified.{' '}
                                                    <Link
                                                        href={send()}
                                                        as="button"
                                                        className="profile__link"
                                                    >
                                                        Click here to re-send
                                                        the verification email.
                                                    </Link>
                                                </p>

                                                {status ===
                                                    'verification-link-sent' && (
                                                    <p className="profile__status">
                                                        A new verification link
                                                        has been sent to your
                                                        email address.
                                                    </p>
                                                )}
                                            </div>
                                        )}
                                </div>

                                <div className="profile__actions">
                                    <button
                                        type="submit"
                                        className="btn btn--primary"
                                        disabled={processing}
                                        data-test="update-profile-button"
                                    >
                                        {processing && <Spinner />}
                                        Save
                                    </button>
                                </div>
                            </>
                        )}
                    </Form>
                </section>

                <DeleteUser />
            </div>
        </>
    );
}
