import { firebaseAuth } from './firebase.js';
import {
    browserLocalPersistence,
    browserSessionPersistence,
    createUserWithEmailAndPassword,
    sendEmailVerification,
    sendPasswordResetEmail,
    setPersistence,
    signInWithEmailAndPassword,
    signOut,
    updateProfile,
} from 'https://www.gstatic.com/firebasejs/13.0.0/firebase-auth.js';

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

function showMessage(element, message, isError = false) {
    if (!element) return;

    element.textContent = message;
    element.classList.toggle('alert-error', isError);
    element.hidden = false;
}

function firebaseErrorMessage(error) {
    switch (error.code) {
        case 'auth/email-already-in-use':
            return 'Un compte utilise déjà cette adresse e-mail. Connectez-vous ou réinitialisez votre mot de passe.';
        case 'auth/invalid-credential':
        case 'auth/user-not-found':
        case 'auth/wrong-password':
            return 'Adresse e-mail ou mot de passe incorrect.';
        case 'auth/invalid-email':
            return 'Saisissez une adresse e-mail valide.';
        case 'auth/weak-password':
            return 'Le mot de passe doit contenir au moins 8 caractères.';
        case 'auth/too-many-requests':
            return 'Trop de tentatives. Réessayez plus tard.';
        case 'auth/network-request-failed':
            return 'Connexion impossible. Vérifiez votre réseau et réessayez.';
        case 'auth/operation-not-allowed':
            return 'La connexion par e-mail doit être activée dans la console Firebase.';
        default:
            return error.message || 'Une erreur est survenue. Réessayez.';
    }
}

async function createLaravelSession(user, whatsapp = null, remember = false) {
    const response = await fetch('/auth/firebase/session', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
            id_token: await user.getIdToken(true),
            whatsapp,
            remember,
        }),
    });
    const result = await response.json();

    if (!response.ok) {
        if (result.code === 'email_verification_required') {
            await sendEmailVerification(user);
            await signOut(firebaseAuth);
            throw new Error('Un lien de vérification a été envoyé. Confirmez cette adresse e-mail, puis reconnectez-vous pour rattacher votre compte existant.');
        }

        throw new Error(result.message || 'La connexion au compte HerveShop a échoué.');
    }

    window.location.assign(result.redirect);
}

document.querySelectorAll('[data-firebase-auth]').forEach((form) => {
    const feedback = form.querySelector('[data-auth-feedback]');

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const submit = form.querySelector('button[type="submit"]');
        submit.disabled = true;

        try {
            const data = new FormData(form);
            const email = String(data.get('email') || '').trim().toLowerCase();
            const password = String(data.get('password') || '');

            if (form.dataset.firebaseAuth === 'register') {
                if (password !== String(data.get('password_confirmation') || '')) {
                    throw new Error('Les deux mots de passe ne correspondent pas.');
                }

                await setPersistence(firebaseAuth, browserSessionPersistence);
                const credential = await createUserWithEmailAndPassword(firebaseAuth, email, password);
                await updateProfile(credential.user, {
                    displayName: String(data.get('name') || '').trim(),
                });
                await createLaravelSession(credential.user, String(data.get('whatsapp') || '').trim() || null);
            } else {
                const remember = form.querySelector('[name="remember"]')?.checked ?? false;
                await setPersistence(firebaseAuth, remember ? browserLocalPersistence : browserSessionPersistence);
                const credential = await signInWithEmailAndPassword(firebaseAuth, email, password);
                await createLaravelSession(credential.user, null, remember);
            }
        } catch (error) {
            showMessage(feedback, firebaseErrorMessage(error), true);
        } finally {
            submit.disabled = false;
        }
    });
});

document.querySelectorAll('[data-firebase-password-reset]').forEach((form) => {
    const feedback = form.querySelector('[data-auth-feedback]');

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const submit = form.querySelector('button[type="submit"]');
        submit.disabled = true;

        try {
            const email = String(new FormData(form).get('email') || '').trim().toLowerCase();
            await sendPasswordResetEmail(firebaseAuth, email);
            showMessage(feedback, 'Si cette adresse correspond à un compte, Firebase lui enverra un lien de réinitialisation.');
        } catch (error) {
            showMessage(feedback, firebaseErrorMessage(error), true);
        } finally {
            submit.disabled = false;
        }
    });
});

document.querySelectorAll('[data-firebase-logout]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        if (form.dataset.firebaseSubmitting === 'true') return;

        event.preventDefault();
        try {
            await signOut(firebaseAuth);
        } catch (error) {
            console.error('La déconnexion Firebase a échoué.', error);
        }

        form.dataset.firebaseSubmitting = 'true';
        form.requestSubmit(event.submitter);
    });
});
