import type { Metadata } from 'next';
import './globals.css';

export const metadata: Metadata = {
  title: 'Test Technique - Gestion Articles',
  description: 'Application de gestion d\'articles avec authentification',
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="fr">
      <body>{children}</body>
    </html>
  );
}
