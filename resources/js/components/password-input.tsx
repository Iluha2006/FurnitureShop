import { Eye, EyeOff } from 'lucide-react';
import type { ComponentProps, Ref } from 'react';
import { useState } from 'react';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';

export default function PasswordInput({
    className,
    ref,
    ...props
}: Omit<ComponentProps<'input'>, 'type'> & { ref?: Ref<HTMLInputElement> }) {
    const [showPassword, setShowPassword] = useState(false);

    return (
        <div className="password-input">
            <Input
                type={showPassword ? 'text' : 'password'}
                className={cn('password-input__field', className)}
                ref={ref}
                {...props}
            />
            <button
                type="button"
                onClick={() => setShowPassword((prev) => !prev)}
                className="password-input__toggle"
                aria-label={showPassword ? 'Скрыть пароль' : 'Показать пароль'}
                aria-pressed={showPassword}
            >
                {showPassword ? (
                    <EyeOff aria-hidden="true" />
                ) : (
                    <Eye aria-hidden="true" />
                )}
            </button>
        </div>
    );
}
