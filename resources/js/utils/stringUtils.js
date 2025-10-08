export function capitalize(value) {
    if (!value) return '';
    return value.charAt(0).toUpperCase() + value.slice(1);
}

export function capitalizeAll(value) {
    if (!value) return '';
    return value.split(' ').map(word => {
        return word.charAt(0).toUpperCase() + word.slice(1);
    }).join(' ');
}

export function isEmpty(value) {
    return value == null || value.trim() == '';
}

export function isNotEmpty(value) {
    console.log('value');
    console.log(value);
    return value != null && value.trim() != '';
}
