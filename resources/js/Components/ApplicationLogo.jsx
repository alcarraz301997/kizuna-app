export default function ApplicationLogo(props) {
    return (
        <svg
            {...props}
            viewBox="0 0 64 64"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
        >
            <path
                d="M28 17H20a11 11 0 0 0 0 22h8a11 11 0 0 0 0-22Z"
                stroke="currentColor"
                strokeWidth="7"
                strokeLinecap="round"
            />
            <path
                d="M36 25h8a11 11 0 0 1 0 22h-8a11 11 0 0 1 0-22Z"
                stroke="currentColor"
                strokeWidth="7"
                strokeLinecap="round"
            />
        </svg>
    );
}
