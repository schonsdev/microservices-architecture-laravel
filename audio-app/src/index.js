require('dotenv').config();
const amqp = require('amqplib');
const mysql = require('mysql2/promise');

const RABBITMQ_QUEUE = 'messages_queue';

async function connectToRabbitMQ() {
    try {
        console.log('Connecting to RabbitMQ...');
        const connection = await amqp.connect({
            hostname: process.env.RABBITMQ_HOST,
            port: parseInt(process.env.RABBITMQ_PORT, 10),
            username: process.env.RABBITMQ_USER,
            password: process.env.RABBITMQ_PASSWORD
        });
        const channel = await connection.createChannel();
        await channel.assertQueue(RABBITMQ_QUEUE, { durable: true });
        console.log('Connected to RabbitMQ');
        return { connection, channel };
    } catch (error) {
        console.error('Failed to connect to RabbitMQ:', error);
        process.exit(1);
    }
}

async function connectToMySQL() {
    try {
        console.log('Connecting to MySQL...');
        const connection = await mysql.createConnection({
            host: process.env.MYSQL_HOST,
            port: parseInt(process.env.MYSQL_PORT, 10),
            user: process.env.MYSQL_USER,
            password: process.env.MYSQL_PASSWORD,
            database: process.env.MYSQL_DATABASE
        });
        console.log('Connected to MySQL');
        return connection;
    } catch (error) {
        console.error('Failed to connect to MySQL:', error);
        process.exit(1);
    }
}

async function processMessage(channel, mysqlConnection, msg) {
    const content = msg.content.toString();
    console.log('Received:', content);

    try {
        const messageData = JSON.parse(content);

        const transcribedText = `Transcribed text for audio at ${messageData.audio_link}`;

        const [result] = await mysqlConnection.execute(
            'UPDATE messages SET message = ? WHERE id = ?',
            [transcribedText, messageData.id]
        );

        console.log('Update to MySQL with ID:', result.insertId);

        channel.ack(msg);
    } catch (error) {
        console.error('Failed to process message:', error);
    }
}

(async () => {
    const { connection, channel } = await connectToRabbitMQ();
    const mysqlConnection = await connectToMySQL();

    console.log('Waiting for messages...');
    channel.consume(RABBITMQ_QUEUE, (msg) => processMessage(channel, mysqlConnection, msg), { noAck: false });

    process.on('SIGINT', () => {
        console.log('Shutting down...');
        channel.close();
        connection.close();
        mysqlConnection.end();
        console.log('Gracefully shut down');
        process.exit(0);
    });
})();