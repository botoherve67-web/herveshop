import { firebaseAuth } from './firebase.js';
import {
    browserLocalPersistence,
    browserSessionPersistence,
    createUserWithEmailAndPassword,
    GoogleAuthProvider,
    linkWithCredential,
    sendEmailVerification,
    sendPasswordResetEmail,
    setPersistence,
    signInWithEmailAndPassword,
    signInWithPopup,
    signOut,
    updateProfile,
} from 'https://www.gstatic.com/firebasejs/13.0.0/firebase-auth.js';

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
const pendingGoogleLinkKey = 'hervershop.pending-google-link';

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
            return 'Cette méthode de connexion doit être activée dans la console Firebase.';
        case 'auth/unauthorized-domain':
            return 'Ajoutez le domaine de ce site aux domaines autorisés dans la console Firebase.';
        case 'auth/popup-closed-by-user':
            return 'La fenêtre de connexion Google a été fermée avant la fin.';
        case 'auth/popup-blocked':
            return 'Le navigateur a bloqué la fenêtre Google. Autorisez les fenêtres surgissantes et réessayez.';
        case 'auth/account-exists-with-different-credential':
            return 'Cette adresse utilise déjà un mot de passe. Connectez-vous par e-mail et mot de passe pour lier Google.';
        case 'auth/credential-already-in-use':
            return 'Ce compte Google est déjà associé à un autre compte HerveShop.';
        default:
            return error.message || 'Une erreur est survenue. Réessayez.';
    }
}

async function linkPendingGoogleCredential(user) {
    const pending = sessionStorage.getItem(pendingGoogleLinkKey);
    if (!pending) return user;

    try {
        const { email, credential, expiresAt } = JSON.parse(pending);
        if (expiresAt <= Date.now()) {
            sessionStorage.removeItem(pendingGoogleLinkKey);
            return user;
        }

        if (email.toLowerCase() !== user.email?.toLowerCase()) {
            return user;
        }

        const result = await linkWithCredential(
            user,
            GoogleAuthProvider.credentialFromJSON(credential),
        );
        sessionStorage.removeItem(pendingGoogleLinkKey);
        return result.user;
    } catch (error) {
        if (error.code === 'auth/invalid-credential' || error.code === 'auth/credential-already-in-use') {
            sessionStorage.removeItem(pendingGoogleLinkKey);
        }
        throw error;
    }
}

async function createLaravelSession(user, whatsapp = null, remember = false, accountType = 'customer') {
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
            account_type: accountType,
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
                await createLaravelSession(
                    credential.user,
                    String(data.get('whatsapp') || '').trim() || null,
                    false,
                    String(data.get('account_type') || 'customer'),
                );
            } else {
                const remember = form.querySelector('[name="remember"]')?.checked ?? false;
                await setPersistence(firebaseAuth, remember ? browserLocalPersistence : browserSessionPersistence);
                const credential = await signInWithEmailAndPassword(firebaseAuth, email, password);
                const user = await linkPendingGoogleCredential(credential.user);
                await createLaravelSession(user, null, remember);
            }
        } catch (error) {
            showMessage(feedback, firebaseErrorMessage(error), true);
        } finally {
            submit.disabled = false;
        }
    });
});

document.querySelectorAll('[data-firebase-google]').forEach((button) => {
    const feedback = button.closest('form')?.querySelector('[data-auth-feedback]');

    button.addEventListener('click', async () => {
        button.disabled = true;
        const form = button.closest('form');
        const remember = form?.querySelector('[name="remember"]')?.checked ?? false;

        try {
            const data = form ? new FormData(form) : null;
            if (form?.dataset.firebaseAuth === 'register') {
                const accountType = String(data?.get('account_type') || 'customer');
                const whatsapp = String(data?.get('whatsapp') || '').trim() || null;
                if (accountType !== 'customer' && !whatsapp) {
                    form.querySelector('[name="whatsapp"]')?.reportValidity();
                    return;
                }
            }
            await setPersistence(
                firebaseAuth,
                remember ? browserLocalPersistence : browserSessionPersistence,
            );
            const result = await signInWithPopup(firebaseAuth, new GoogleAuthProvider());
            await createLaravelSession(
                result.user,
                form?.dataset.firebaseAuth === 'register' ? String(data?.get('whatsapp') || '').trim() || null : null,
                remember,
                form?.dataset.firebaseAuth === 'register' ? String(data?.get('account_type') || 'customer') : 'customer',
            );
        } catch (error) {
            if (error.code === 'auth/account-exists-with-different-credential') {
                const credential = GoogleAuthProvider.credentialFromError(error);
                const email = error.customData?.email;

                if (credential && email) {
                    sessionStorage.setItem(pendingGoogleLinkKey, JSON.stringify({
                        email,
                        credential: credential.toJSON(),
                        expiresAt: Date.now() + 5 * 60 * 1000,
                    }));
                    showMessage(
                        feedback,
                        'Cette adresse utilise déjà un mot de passe. Connectez-vous ci-dessous par e-mail pour lier votre compte Google.',
                    );
                } else {
                    showMessage(feedback, firebaseErrorMessage(error), true);
                }
            } else {
                showMessage(feedback, firebaseErrorMessage(error), true);
            }
        } finally {
            button.disabled = false;
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
