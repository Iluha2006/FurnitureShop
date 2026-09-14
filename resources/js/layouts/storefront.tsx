import type { ReactNode } from 'react';
import Footer from '@/layouts/Footer';
import Header from '@/layouts/Header';

type Props = {
    children: ReactNode;
};

export default function StorefrontLayout({ children }: Props) {
    return (
        <div className="app">
            <Header />
            <main>{children}</main>
            <Footer />
        </div>
    );
}