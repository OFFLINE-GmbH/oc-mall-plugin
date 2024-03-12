import fs from 'node:fs';
import path from 'node:path';
import Fastify from 'fastify';
import Localizer from './lib/localizer.js';

// Create fastify server
const fastify = Fastify({
    logger: {
        transport: {
            target: '@fastify/one-line-logger'
        }
    }
});

// Set no-cache Hook
fastify.addHook('onRequest', (request, reply, done) => {
    reply.headers({
        'Cache-Control': 'no-store, max-age=0, must-revalidate',
        'Expires': '0',
        'Pragma': 'no-cache',
        'Surrogate-Control': 'no-store',
        'X-Powered-By': 'OCTranslator @ fastify',
    });
    done();
});

// Set Localizer
fastify.decorate('localizer', async () => {
    if (Localizer.hasInstance()) {
        return Localizer.instance;
    } else {
        const localizer = new Localizer(process.cwd());
        await localizer.readLocales();
        await localizer.readSources();
        return localizer;
    }
})

// Home Route
fastify.get('/', async (request, reply) => {
    let file = path.resolve(process.cwd(), 'src', 'localize', 'client', 'index.html');
    let content = await fs.promises.readFile(file);

    reply.status(200).type('text/html').send(content);
});

// GET known locales
fastify.get('/locales', async (request, reply) => {
    try {
        const localizer = await fastify.localizer();
        reply.status(200).send({ status: 'success', result: localizer.locales });
    } catch (err) {
        console.error(err);
        reply.status(500).send({ status: 'error', message: err.message });
    }
});

// GET Dashboard translation states
fastify.get('/stats/:locale', async (request, reply) => {
    try {
        const localizer = await fastify.localizer();
        const result = await localizer.stats(request.params.locale.toLowerCase());
        reply.status(200).send({ status: 'success', result });
    } catch (err) {
        console.error(err);
        reply.status(500).send({ status: 'error', message: err.message });
    }
});

// GET Strings
fastify.get('/strings/:locale', async (request, reply) => {
    try {
        const localizer = await fastify.localizer();
        const result = await localizer.fetchStrings(request.params.locale.toLowerCase());
        reply.status(200).send({ status: 'success', result });
    } catch (err) {
        console.error(err);
        reply.status(500).send({ status: 'error', message: err.message });
    }
});

// POST String
fastify.post('/save/:locale/:file/:key', async (request, reply) => {
    try {
        const localizer = await fastify.localizer();
        const result = await localizer.updateString(
            request.params.locale.toLowerCase(),
            request.params.file,
            request.params.key,
            request.body,
        );
        reply.status(200).send({ status: 'success', result });
    } catch (err) {
        console.error(err);
        reply.status(500).send({ status: 'error', message: err.message });
    }
});

// Run the server
try {
    await fastify.listen({ port: 3005 });
} catch (err) {
    fastify.log.error(err);
    process.exit(1);
}
