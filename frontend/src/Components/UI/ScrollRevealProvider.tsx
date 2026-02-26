import type React from "react";
import { useEffect } from "react";

/**
 * Observa elementos com a classe `reveal` e adiciona `reveal-visible`
 * quando eles entram na viewport. Executa apenas no client.
 */
export default function ScrollRevealProvider({
  children,
}: {
  children: React.ReactNode;
}) {
  useEffect(() => {
    const reduceMotion =
      typeof window !== "undefined" &&
      window.matchMedia?.("(prefers-reduced-motion: reduce)")?.matches;

    if (reduceMotion) {
      document
        .querySelectorAll<HTMLElement>(".reveal")
        .forEach((el) => el.classList.add("reveal-visible"));
      return;
    }

    const els = Array.from(document.querySelectorAll<HTMLElement>(".reveal"));
    if (els.length === 0) return;

    const io = new IntersectionObserver(
      (entries) => {
        for (const entry of entries) {
          if (!entry.isIntersecting) continue;
          const el = entry.target as HTMLElement;
          el.classList.add("reveal-visible");
          io.unobserve(el);
        }
      },
      { threshold: 0.12, rootMargin: "0px 0px -8% 0px" }
    );

    els.forEach((el) => io.observe(el));

    return () => io.disconnect();
  }, []);

  return <>{children}</>;
}
