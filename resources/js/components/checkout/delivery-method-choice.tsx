import { Store, Truck } from 'lucide-react';
import InputError from '@/components/input-error';
import type { DeliveryMethod } from '@/types/pickupPoint';

interface DeliveryMethodChoiceProps {
    value: DeliveryMethod;
    onChange: (method: DeliveryMethod) => void;
    error?: string;
}

const METHODS: Array<{
    value: DeliveryMethod;
    icon: typeof Truck;
    title: string;
    text: string;
}> = [
    {
        value: 'delivery',
        icon: Truck,
        title: 'Доставка курьером',
        text: 'Привезём по адресу, время согласуем по телефону',
    },
    {
        value: 'pickup',
        icon: Store,
        title: 'Самовывоз из пункта выдачи',
        text: 'Бесплатно, заберите в удобное время',
    },
];

export default function DeliveryMethodChoice({
    value,
    onChange,
    error,
}: DeliveryMethodChoiceProps) {
    return (
        <>
            <div className="checkout__methods">
                {METHODS.map((method) => (
                    <label
                        key={method.value}
                        className={
                            value === method.value
                                ? 'checkout-method checkout-method--active'
                                : 'checkout-method'
                        }
                    >
                        <input
                            type="radio"
                            name="delivery_method"
                            value={method.value}
                            checked={value === method.value}
                            onChange={() => onChange(method.value)}
                            className="checkout-method__radio"
                        />
                        <span className="checkout-method__icon">
                            <method.icon size={20} aria-hidden="true" />
                        </span>
                        <span className="checkout-method__body">
                            <b>{method.title}</b>
                            <span>{method.text}</span>
                        </span>
                    </label>
                ))}
            </div>

            <InputError message={error} className="auth-error" />
        </>
    );
}
