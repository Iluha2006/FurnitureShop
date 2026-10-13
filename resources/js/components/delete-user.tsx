import { Form } from '@inertiajs/react';
import { useRef } from 'react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';

export default function DeleteUser() {
    const passwordInput = useRef<HTMLInputElement>(null);

    return (
        <section className="profile__panel">
            <h2 className="profile__title">Delete account</h2>
            <p className="profile__desc">
                Delete your account and all of its resources
            </p>

            <div className="profile__danger">
                <p className="profile__danger-title">Warning</p>
                <p className="profile__danger-desc">
                    Please proceed with caution, this cannot be undone.
                </p>

                <Dialog>
                    <DialogTrigger asChild>
                        <button
                            type="button"
                            className="btn btn--outline"
                            data-test="delete-user-button"
                        >
                            Delete account
                        </button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogTitle>
                            Are you sure you want to delete your account?
                        </DialogTitle>
                        <DialogDescription>
                            Once your account is deleted, all of its resources
                            and data will also be permanently deleted. Please
                            enter your password to confirm you would like to
                            permanently delete your account.
                        </DialogDescription>

                        <Form
                            {...ProfileController.destroy.form()}
                            options={{
                                preserveScroll: true,
                            }}
                            onError={() => passwordInput.current?.focus()}
                            resetOnSuccess
                        >
                            {({ resetAndClearErrors, processing, errors }) => (
                                <>
                                    <div className="profile__field">
                                        <Label
                                            htmlFor="password"
                                            className="sr-only"
                                        >
                                            Password
                                        </Label>

                                        <PasswordInput
                                            id="password"
                                            name="password"
                                            ref={passwordInput}
                                            placeholder="Password"
                                            autoComplete="current-password"
                                            className="profile__input"
                                        />

                                        <InputError
                                            className="profile__error"
                                            message={errors.password}
                                        />
                                    </div>

                                    <DialogFooter>
                                        <DialogClose asChild>
                                            <button
                                                type="button"
                                                className="btn btn--outline"
                                                onClick={() =>
                                                    resetAndClearErrors()
                                                }
                                            >
                                                Cancel
                                            </button>
                                        </DialogClose>

                                        <button
                                            type="submit"
                                            className="btn btn--primary"
                                            disabled={processing}
                                            data-test="confirm-delete-user-button"
                                        >
                                            Delete account
                                        </button>
                                    </DialogFooter>
                                </>
                            )}
                        </Form>
                    </DialogContent>
                </Dialog>
            </div>
        </section>
    );
}
