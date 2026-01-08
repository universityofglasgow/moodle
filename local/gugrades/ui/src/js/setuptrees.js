/**
 * Composable to populate the trees of grade categories
 * and items when MyGrades loads. Done as this process 
 * can be slow. 
 */

import { useActivityTreeStore } from '../stores/activitytree.js';

export function usePopulateTrees() {

    const populate = () => {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        // Get all the level 1 categories.
        fetchMany([{
            methodname: 'local_gugrades_get_levelonecategories',
            args: {
                courseid: courseid,
            }
        }])[0]
        .then(result => {
            const activitytree = useActivityTreeStore();
            let promises = []; 
            result.forEach(cat => {
                const catid = cat.id;

                promises.push(

                    // Get the (detailed) tree for this top level category.
                    fetchMany([{
                        methodname: 'local_gugrades_get_activities',
                        args: {
                            courseid: courseid,
                            categoryid: catid,
                            detailed: true,
                        }
                    }])[0]
                    .then(result => {
                        activitytree.trees[catid] = result.activities;
                        activitytree.errors[catid] = result.error;
                    })
                    .catch(error => {
                        console.error(error);
                    })
                );
            });

            Promise.all(promises).then(() => {
                activitytree.ready = true;
                console.log('all done');
            })
        })
        .catch(error => {
            console.error(error);
        });
    }

    return { populate };
}