const express = require('express');
const http = require('http');
const socketIo = require('socket.io');

const app = express();
const server = http.createServer(app);
const io = socketIo(server, { cors: { origin: "*" } });  // Cho phép CORS từ PHP pages

// Xử lý POST /emit từ PHP curl
app.use(express.json());
app.post('/emit', (req, res) => {
    const { event, room, data } = req.body;
    io.to(room).emit(event, data);  // Emit đến tất cả client trong room
    res.json({ status: 'OK' });
});

// Socket connections
io.on('connection', (socket) => {
    console.log('Client connected:', socket.id);

    // Join room khi client emit 'join-room'
    socket.on('join-room', (room) => {
        socket.join(room);
        console.log(`Socket ${socket.id} joined room: ${room}`);
    });

    socket.on('disconnect', () => {
        console.log('Client disconnected:', socket.id);
    });
});

server.listen(4000, () => {
    console.log('Socket.IO server running on port 4000');
});