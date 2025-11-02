'use client';

export default function Footer() {
  return (
    <footer className="bg-gray-800 text-white mt-auto py-6">
      <div className="container mx-auto px-20">
        <div className="text-center">
          <p className="text-sm text-gray-400">
            © {new Date().getFullYear()} Tous droits réservés.
          </p>
        </div>
      </div>
    </footer>
  );
}
