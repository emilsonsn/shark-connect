export default (date) => {
    return date ? new Date(date)?.toLocaleString("pt-BR"): "";
}