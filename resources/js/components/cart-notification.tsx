type Props = {
    furnitureName: string;
};

export default function CartNotification({ furnitureName }: Props) {
    return (
        <span className="cart-notification">
            <span className="cart-notification__name">{furnitureName}</span>
        </span>
    );
}
