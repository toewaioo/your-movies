import { jsx } from "react/jsx-runtime";
/* empty css     */
import axios from "axios";
import { createInertiaApp } from "@inertiajs/react";
import { createRoot } from "react-dom/client";
window.axios = axios;
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
async function resolvePageComponent(path, pages) {
  for (const p of Array.isArray(path) ? path : [path]) {
    const page = pages[p];
    if (typeof page === "undefined") {
      continue;
    }
    return typeof page === "function" ? page() : page;
  }
  throw new Error(`Page not found: ${path}`);
}
const appName = "Laravel";
createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) => resolvePageComponent(
    `./Pages/${name}.jsx`,
    /* @__PURE__ */ Object.assign({ "./Pages/Auth/ConfirmPassword.jsx": () => import("./assets/ConfirmPassword-zJiiJlnj.js"), "./Pages/Auth/ForgotPassword.jsx": () => import("./assets/ForgotPassword-DFfhu8Sk.js"), "./Pages/Auth/Login.jsx": () => import("./assets/Login-DCJsqiN-.js"), "./Pages/Auth/Register.jsx": () => import("./assets/Register-OdfkC5WB.js"), "./Pages/Auth/ResetPassword.jsx": () => import("./assets/ResetPassword-BJMV7pbW.js"), "./Pages/Auth/VerifyEmail.jsx": () => import("./assets/VerifyEmail-3DaxC1ow.js"), "./Pages/Dashboard.jsx": () => import("./assets/Dashboard-DOCLXSKk.js"), "./Pages/NotFound.jsx": () => import("./assets/NotFound-D1mP9SXS.js"), "./Pages/Profile/Edit.jsx": () => import("./assets/Edit-BFeM5QDF.js"), "./Pages/Profile/Partials/DeleteUserForm.jsx": () => import("./assets/DeleteUserForm-Hy-SY24R.js"), "./Pages/Profile/Partials/UpdatePasswordForm.jsx": () => import("./assets/UpdatePasswordForm-zvD8TgBd.js"), "./Pages/Profile/Partials/UpdateProfileInformationForm.jsx": () => import("./assets/UpdateProfileInformationForm-DmOzctMU.js"), "./Pages/Public/HomePage.jsx": () => import("./assets/HomePage-BCf6QRo-.js"), "./Pages/Public/MovieDetail.jsx": () => import("./assets/MovieDetail-BrHja0Pl.js"), "./Pages/Public/MoviesPage.jsx": () => import("./assets/MoviesPage-__XUt4jT.js"), "./Pages/Public/SeriesDetail.jsx": () => import("./assets/SeriesDetail-Ds7-rFHk.js"), "./Pages/Public/SeriesPage.jsx": () => import("./assets/SeriesPage-BYZNaGzP.js"), "./Pages/Welcome.jsx": () => import("./assets/Welcome-SEhQsnTJ.js") })
  ),
  setup({ el, App, props }) {
    const root = createRoot(el);
    root.render(/* @__PURE__ */ jsx(App, { ...props }));
  },
  progress: {
    color: "#4B5563"
  }
});
