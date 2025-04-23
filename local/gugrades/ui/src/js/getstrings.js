/**
 * Loads strings from Moodle WS
 */

export async function getstrings(list) {

    const GU = window.GU;

    const strings = list.map((name) => {
        return {
            key: name,
            component: 'local_gugrades'
        }
    });

    const sfetch = GU.getStrings(strings)
    .then((result) => {
        let value = 0;
        const translated = {};
        list.forEach((name, i) => {
            value = result[i];
            translated[name] = value;
        });
        return translated;
    });

    return sfetch;
}