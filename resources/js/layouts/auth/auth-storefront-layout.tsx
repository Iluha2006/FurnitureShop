import { Link } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';

export default function AuthStorefrontLayout({
    title = '',
    description = '',
    children,
}: PropsWithChildren<AuthLayoutProps>) {
    return (
        <div className="auth-page">
            <div className="auth-wrap">
                <Link href={home()} className="logo auth-brand">
                    <span className="logo__mark">М</span>
                    <span className="logo__text">
                        <strong>МебельДом</strong>
                        <small>корпусная и мягкая мебель</small>
                    </span>
                </Link>

                <div className="auth-card">
                    <header className="auth-header">
                        <h1>{title}</h1>
                        <p>{description}</p>
                    </header>
                    {children}
                </div>

                <ul className="auth-trust">
                    <li>Доставка и сборка</li>
                    <li>Гарантия 24 месяца</li>
                    <li>Рассрочка 0%</li>
                </ul>
            </div>
        </div>
    );
}