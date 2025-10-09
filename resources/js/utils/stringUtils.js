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

export function isNotEmpty(value) {
  return !isEmpty(value);
}

export function isEmpty(value) {

  // Handle null or undefined
  if (value == null) {
    return true;
  }

  // Handle strings
  if (typeof value === 'string') {
    return value.trim() === '';
  }

  // Handle objects (including arrays)
  if (typeof value === 'object') {
    return Object.keys(value).length === 0;
  }

  // Handle other types (numbers, booleans, etc.)
  return false;

}
