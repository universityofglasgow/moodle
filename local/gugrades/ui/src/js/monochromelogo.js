import { ref } from 'vue';

const monochrome = ref(false);

export function useLogo() {

    const updateLogo = () => {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        fetchMany([{
            methodname: 'local_gugrades_get_dashboard_enabled',
            args: {
                courseid: courseid,
            }
        }])[0]
        .then((result) => {
            const enabled = result.enabled;
            monochrome.value = !enabled;
        })
        .catch((error) => {
            window.console.error(error);
        });
    }

    return {
        monochrome,
        updateLogo,
    };
}