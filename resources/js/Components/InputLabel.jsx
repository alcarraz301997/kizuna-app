export default function InputLabel({
    value,
    className = '',
    children,
    ...props
}) {
    return (
        <label
            {...props}
            className={
                `block text-sm sm:text-base font-semibold text-gray-700 ` +
                className
            }
        >
            {value ? value : children}
        </label>
    );
}
