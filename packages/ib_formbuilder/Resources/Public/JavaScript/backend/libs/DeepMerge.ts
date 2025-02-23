/**
* Simple object check.
* @param item
* @returns {boolean}
*/
export function isObject(item:any):boolean {
    return (item && typeof item === 'object' && !Array.isArray(item));
}

/**
* Deep merge two objects.
* @param target
* @param ...sources
*/
export function mergeDeep(target:any, ...sources:Array<object>) {
    if (!sources.length) return target;
    const source:any = sources.shift()!;

    if (isObject(target) && isObject(source)) {
        for (const key in source) {
            if (isObject(source[key])) {
                if (!target[key]) Object.assign(target, { [key]: {} });
                mergeDeep(target[key], source[key]);
            } else {
                Object.assign(target, { [key]: source[key] });
            }
        }
    }

    return mergeDeep(target, ...sources);
}

// Code copied from linked Stack Overflow question
// https://stackoverflow.com/questions/27936772/how-to-deep-merge-instead-of-shallow-merge
// Answer by Salakar:
// https://stackoverflow.com/users/2938161/salakar
