
/**
 * Wait Handler
 * @param {number} ms The number of ms to wait.
 * @returns {Promise}
 */
function wait(ms: number): Promise<null> {
    return new Promise(resolve => {
        setTimeout(resolve.bind(null ,null), ms);
    });
}

// Export Module
export default wait;
