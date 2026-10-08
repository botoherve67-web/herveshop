import { initializeApp } from 'https://www.gstatic.com/firebasejs/13.0.0/firebase-app.js';
import { getAuth } from 'https://www.gstatic.com/firebasejs/13.0.0/firebase-auth.js';

const firebaseConfig = {
    apiKey: 'AIzaSyBl09R-IVthqA6AAraMRRUH1ye7oFijtsc',
    authDomain: 'elledji.firebaseapp.com',
    projectId: 'elledji',
    storageBucket: 'elledji.firebasestorage.app',
    messagingSenderId: '601439462375',
    appId: '1:601439462375:web:e741d3290e86efe9a21342',
};

export const firebaseApp = initializeApp(firebaseConfig);
export const firebaseAuth = getAuth(firebaseApp);
window.firebaseApp = firebaseApp;
