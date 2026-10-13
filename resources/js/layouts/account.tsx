import type { ReactNode } from 'react';
import AccountNav from '@/components/account-nav';
import Footer from '@/layouts/Footer';
import Header from '@/layouts/Header';

type Props = {
    children: ReactNode;
};

export default function AccountLayout({ children }: Props) {
    return (
        <div className="app">
            <Header />

            <main>
                <div className="home-layout container">
                    <AccountNav />

                    <div className="home-content">{children}</div>
                </div>
            </main>

            <Footer />
        </div>
    );
}
