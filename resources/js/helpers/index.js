import dayjs from "dayjs";
import "dayjs/locale/pt-br";
import timezone from "dayjs/plugin/timezone";
import utc from "dayjs/plugin/utc";

dayjs.extend(utc);
dayjs.extend(timezone);
dayjs.locale("pt-br");

export const formatPrice = (value) => {
  if (value === null || value === undefined || value === "") {
    return "R$ 0,00";
  }

  return new Intl.NumberFormat("pt-BR", {
    style: "currency",
    currency: "BRL",
    minimumFractionDigits: 2,
  }).format(Number(value));
};

export const formatDate = (value, pattern = "DD/MM/YYYY") => {
  if (!value) {
    return "-";
  }

  const date = dayjs(value);

  return date.isValid() ? date.format(pattern) : "-";
};
